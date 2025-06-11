/**
 * Zero-Knowledge Blog Cryptography Functions
 * Handles client-side encryption and decryption using Web Crypto API
 */

window.ZKBCrypto = (function() {
    'use strict';
    
    // Check if Web Crypto API is available
    if (!window.crypto || !window.crypto.subtle) {
        console.error('Web Crypto API not supported');
        return null;
    }
    
    const crypto = window.crypto;
    const subtle = crypto.subtle;
    
    /**
     * Generate cryptographically secure random bytes
     */
    function generateRandomBytes(length) {
        return crypto.getRandomValues(new Uint8Array(length));
    }
    
    /**
     * Convert string to ArrayBuffer
     */
    function stringToArrayBuffer(str) {
        const encoder = new TextEncoder();
        return encoder.encode(str);
    }
    
    /**
     * Convert ArrayBuffer to string
     */
    function arrayBufferToString(buffer) {
        const decoder = new TextDecoder();
        return decoder.decode(buffer);
    }
    
    /**
     * Convert ArrayBuffer to base64 string
     */
    function arrayBufferToBase64(buffer) {
        const bytes = new Uint8Array(buffer);
        let binary = '';
        for (let i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return btoa(binary);
    }
    
    /**
     * Convert base64 string to ArrayBuffer
     */
    function base64ToArrayBuffer(base64) {
        const binary = atob(base64);
        const bytes = new Uint8Array(binary.length);
        for (let i = 0; i < binary.length; i++) {
            bytes[i] = binary.charCodeAt(i);
        }
        return bytes.buffer;
    }
    
    /**
     * Derive encryption key from password using PBKDF2
     */
    async function deriveKey(password, salt, iterations = 100000) {
        const passwordBuffer = stringToArrayBuffer(password);
        
        // Import password as key material
        const keyMaterial = await subtle.importKey(
            'raw',
            passwordBuffer,
            { name: 'PBKDF2' },
            false,
            ['deriveKey']
        );
        
        // Derive AES key
        return await subtle.deriveKey(
            {
                name: 'PBKDF2',
                salt: salt,
                iterations: iterations,
                hash: 'SHA-256'
            },
            keyMaterial,
            {
                name: 'AES-GCM',
                length: 256
            },
            false,
            ['encrypt', 'decrypt']
        );
    }
    
    /**
     * Encrypt content with password
     */
    async function encryptContent(content, password) {
        try {
            // Generate random salt and IV
            const salt = generateRandomBytes(16);
            const iv = generateRandomBytes(12);
            
            // Derive key from password
            const key = await deriveKey(password, salt);
            
            // Encrypt content
            const contentBuffer = stringToArrayBuffer(content);
            const encryptedBuffer = await subtle.encrypt(
                {
                    name: 'AES-GCM',
                    iv: iv
                },
                key,
                contentBuffer
            );
            
            return {
                encrypted: arrayBufferToBase64(encryptedBuffer),
                salt: arrayBufferToBase64(salt),
                iv: arrayBufferToBase64(iv)
            };
            
        } catch (error) {
            console.error('Encryption failed:', error);
            throw new Error('Encryption failed');
        }
    }
    
    /**
     * Decrypt content with password
     */
    async function decryptContent(encryptedData, password) {
        try {
            // Convert base64 back to ArrayBuffers
            const encryptedBuffer = base64ToArrayBuffer(encryptedData.encrypted);
            const salt = base64ToArrayBuffer(encryptedData.salt);
            const iv = base64ToArrayBuffer(encryptedData.iv);
            
            // Derive key from password
            const key = await deriveKey(password, salt);
            
            // Decrypt content
            const decryptedBuffer = await subtle.decrypt(
                {
                    name: 'AES-GCM',
                    iv: iv
                },
                key,
                encryptedBuffer
            );
            
            return arrayBufferToString(decryptedBuffer);
            
        } catch (error) {
            console.error('Decryption failed:', error);
            throw new Error('Wrong password or corrupted data');
        }
    }
    
    /**
     * Check password strength
     */
    function checkPasswordStrength(password) {
        let score = 0;
        let feedback = [];
        
        // Length check
        if (password.length >= 12) {
            score += 2;
        } else if (password.length >= 8) {
            score += 1;
            feedback.push('Use at least 12 characters');
        } else {
            feedback.push('Password too short (minimum 8 characters)');
        }
        
        // Character variety checks
        if (/[a-z]/.test(password)) score += 1;
        else feedback.push('Add lowercase letters');
        
        if (/[A-Z]/.test(password)) score += 1;
        else feedback.push('Add uppercase letters');
        
        if (/[0-9]/.test(password)) score += 1;
        else feedback.push('Add numbers');
        
        if (/[^A-Za-z0-9]/.test(password)) score += 1;
        else feedback.push('Add special characters');
        
        // Determine strength level
        let strength = 'very-weak';
        if (score >= 6) strength = 'strong';
        else if (score >= 4) strength = 'medium';
        else if (score >= 2) strength = 'weak';
        
        return {
            score: score,
            strength: strength,
            feedback: feedback,
            isStrong: score >= 5
        };
    }
    
    /**
     * Generate secure random password
     */
    function generatePassword(length = 16) {
        const charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
        const randomBytes = generateRandomBytes(length);
        let password = '';
        
        for (let i = 0; i < length; i++) {
            password += charset[randomBytes[i] % charset.length];
        }
        
        return password;
    }
    
    // Public API
    return {
        encryptContent: encryptContent,
        decryptContent: decryptContent,
        checkPasswordStrength: checkPasswordStrength,
        generatePassword: generatePassword,
        isSupported: function() {
            return !!(window.crypto && window.crypto.subtle);
        }
    };
    
})();

// Check browser compatibility on load
if (!ZKBCrypto || !ZKBCrypto.isSupported()) {
    console.error('ZKB: Browser does not support required cryptographic features');
}
