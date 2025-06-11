/**
 * Zero-Knowledge Blog Admin JavaScript - FIXED METHOD NAMES
 */

jQuery(document).ready(function($) {
    console.log('ZKB: Starting initialization...');
    
    let isEncrypting = false;
    
    // Initialize immediately and keep trying
    initializeZKB();
    setTimeout(initializeZKB, 500);
    setTimeout(initializeZKB, 1000);
    setTimeout(initializeZKB, 2000);
    
    function initializeZKB() {
        console.log('ZKB: Initializing...');
        
        const $encryptBtn = $('#zkb-encrypt-btn');
        const $passwordField = $('#zkb-password');
        const $confirmField = $('#zkb-password-confirm');
        
        if ($encryptBtn.length === 0) {
            console.log('ZKB: Button not found yet, will retry...');
            return;
        }
        
        console.log('ZKB: Elements found, setting up...');
        
        // Aggressively enable button
        enableButton();
        
        // Keep enabling button every second
        setInterval(function() {
            if (!isEncrypting) {
                enableButton();
            }
        }, 1000);
        
        // Bind events (remove existing first)
        $encryptBtn.off('click.zkb').on('click.zkb', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('ZKB: Encrypt button clicked!');
            handleEncrypt();
        });
        
        $passwordField.off('input.zkb').on('input.zkb', function() {
            const password = $(this).val();
            console.log('ZKB: Password changed, length:', password.length);
            updatePasswordStrength(password);
            enableButton(); // Re-enable after password change
        });
        
        $confirmField.off('input.zkb').on('input.zkb', function() {
            const confirm = $(this).val();
            console.log('ZKB: Confirm password changed, length:', confirm.length);
            enableButton(); // Re-enable after confirm change
        });
        
        console.log('ZKB: Setup complete');
    }
    
    function enableButton() {
        const $btn = $('#zkb-encrypt-btn');
        if ($btn.length && !isEncrypting) {
            $btn.prop('disabled', false)
                .removeClass('disabled')
                .css({
                    'opacity': '1',
                    'pointer-events': 'auto',
                    'cursor': 'pointer'
                });
        }
    }
    
    function handleEncrypt() {
        if (isEncrypting) {
            console.log('ZKB: Already encrypting, ignoring click');
            return;
        }
        
        console.log('ZKB: Starting encryption process...');
        
        const password = $('#zkb-password').val().trim();
        const confirmPassword = $('#zkb-password-confirm').val().trim();
        
        console.log('Password validation:');
        console.log('- Password length:', password.length);
        console.log('- Confirm length:', confirmPassword.length);
        console.log('- Match:', password === confirmPassword);
        
        // Validation
        if (!password) {
            showMessage('Please enter a password.', 'error');
            return;
        }
        
        if (password.length < 4) {
            showMessage('Password must be at least 4 characters long.', 'error');
            return;
        }
        
        if (!confirmPassword) {
            showMessage('Please confirm your password.', 'error');
            return;
        }
        
        if (password !== confirmPassword) {
            showMessage('Passwords do not match.', 'error');
            return;
        }
        
        // Get content from editor
        let content = '';
        if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
            try {
                content = wp.data.select('core/editor').getEditedPostAttribute('content') || '';
                console.log('ZKB: Content found, length:', content.length);
            } catch (e) {
                console.error('ZKB: Error getting content:', e);
            }
        }
        
        if (!content.trim()) {
            showMessage('No content to encrypt. Please add some content to your post first.', 'error');
            return;
        }
        
        // Check if crypto library is available
        if (typeof ZKBCrypto === 'undefined') {
            showMessage('Crypto library not loaded. Please refresh the page.', 'error');
            console.error('ZKB: ZKBCrypto not available');
            return;
        }
        
        // Check if the correct method exists
        if (typeof ZKBCrypto.encryptContent !== 'function') {
            showMessage('Crypto library method not available. Available methods: ' + Object.keys(ZKBCrypto).join(', '), 'error');
            console.error('ZKB: encryptContent method not available');
            return;
        }
        
        // Start encryption
        encryptContent(content, password);
    }
    
    async function encryptContent(content, password) {
    try {
        isEncrypting = true;
        console.log('ZKB: Starting encryption...');
        
        // Show loading
        $('#zkb-encrypt-btn').text('🔄 Encrypting...');
        showMessage('Encrypting content...', 'info');
        
        // Use the correct method name: encryptContent
        console.log('ZKB: Calling ZKBCrypto.encryptContent...');
        const encryptedResult = await ZKBCrypto.encryptContent(content, password);
        console.log('ZKB: Encryption result type:', typeof encryptedResult);
        console.log('ZKB: Encryption result:', encryptedResult);
        
        // CRITICAL FIX: Convert the result to JSON string for storage
        const encryptedContentString = JSON.stringify(encryptedResult);
        console.log('ZKB: JSON string length:', encryptedContentString.length);
        console.log('ZKB: JSON string preview:', encryptedContentString.substring(0, 100) + '...');
        
        // Create shortcode with JSON string
        const shortcode = `[zkb_encrypted_content]${encryptedContentString}[/zkb_encrypted_content]`;
        console.log('ZKB: Final shortcode:', shortcode);
        
        // Try multiple methods to set content in Block Editor
        let contentSet = false;
        
        // Method 1: Try Block Editor dispatch
        if (typeof wp !== 'undefined' && wp.data && wp.data.dispatch('core/editor')) {
            try {
                console.log('ZKB: Trying Block Editor method...');
                
                // Clear all blocks first
                const blocks = wp.data.select('core/block-editor').getBlocks();
                console.log('ZKB: Current blocks:', blocks.length);
                
                // Remove all blocks
                const blockClientIds = blocks.map(block => block.clientId);
                if (blockClientIds.length > 0) {
                    wp.data.dispatch('core/block-editor').removeBlocks(blockClientIds);
                }
                
                // Create a new HTML block with our shortcode
                const htmlBlock = wp.blocks.createBlock('core/html', {
                    content: shortcode
                });
                
                // Insert the new block
                wp.data.dispatch('core/block-editor').insertBlocks([htmlBlock]);
                
                console.log('ZKB: Content set via Block Editor');
                contentSet = true;
                
            } catch (blockError) {
                console.error('ZKB: Block Editor method failed:', blockError);
            }
        }
        
        // Method 2: Try classic editor method as fallback
        if (!contentSet && typeof wp !== 'undefined' && wp.data && wp.data.dispatch('core/editor')) {
            try {
                console.log('ZKB: Trying classic editor method...');
                wp.data.dispatch('core/editor').editPost({ content: shortcode });
                console.log('ZKB: Content set via classic editor method');
                contentSet = true;
            } catch (classicError) {
                console.error('ZKB: Classic editor method failed:', classicError);
            }
        }
        
        // Method 3: Direct textarea manipulation as last resort
        if (!contentSet) {
            console.log('ZKB: Trying direct textarea method...');
            const textarea = document.querySelector('#content');
            if (textarea) {
                textarea.value = shortcode;
                console.log('ZKB: Content set via direct textarea');
                contentSet = true;
            }
        }
        
        if (contentSet) {
            showMessage('✅ Content encrypted successfully! Remember to save your post.', 'success');
            
            // Clear passwords
            $('#zkb-password, #zkb-password-confirm').val('');
            
            // Verify the content was actually set
            setTimeout(() => {
                if (typeof wp !== 'undefined' && wp.data && wp.data.select('core/editor')) {
                    const newContent = wp.data.select('core/editor').getEditedPostAttribute('content');
                    console.log('ZKB: Verification - new content contains shortcode:', newContent.includes('[zkb_encrypted_content]'));
                    console.log('ZKB: Verification - new content length:', newContent.length);
                    
                    if (!newContent.includes('[zkb_encrypted_content]')) {
                        showMessage('⚠️ Warning: Content may not have been set properly. Please check and save manually.', 'warning');
                    }
                }
            }, 1000);
            
        } else {
            throw new Error('Could not set encrypted content in editor');
        }
        
    } catch (error) {
        console.error('ZKB: Encryption failed:', error);
        showMessage('❌ Encryption failed: ' + error.message, 'error');
    } finally {
        isEncrypting = false;
        $('#zkb-encrypt-btn').text('🔒 Encrypt Content');
        enableButton();
    }
}

    
    function updatePasswordStrength(password) {
        // Use ZKBCrypto's built-in password strength checker if available
        if (typeof ZKBCrypto !== 'undefined' && typeof ZKBCrypto.checkPasswordStrength === 'function') {
            try {
                const strength = ZKBCrypto.checkPasswordStrength(password);
                console.log('ZKB: Password strength from library:', strength);
                return;
            } catch (e) {
                console.log('ZKB: Using fallback password strength checker');
            }
        }
        
        // Fallback strength checker
        let strength = 0;
        let feedback = 'Enter password';
        
        if (password.length >= 4) strength += 25;
        if (password.length >= 8) strength += 25;
        if (/[a-z]/.test(password)) strength += 25;
        if (/[A-Z]/.test(password)) strength += 25;
        if (/[0-9]/.test(password)) strength += 25;
        if (/[^A-Za-z0-9]/.test(password)) strength += 25;
        
        if (password.length > 0) {
            if (strength < 50) {
                feedback = 'Weak';
            } else if (strength < 75) {
                feedback = 'Medium';
            } else {
                feedback = 'Strong';
            }
        }
        
        console.log('ZKB: Password strength:', feedback, strength + '%');
    }
    
    function showMessage(message, type) {
        console.log('ZKB Message (' + type + '):', message);
        
        const $messages = $('#zkb-messages');
        if ($messages.length) {
            const messageHtml = `<div class="notice notice-${type} is-dismissible"><p>${message}</p></div>`;
            $messages.html(messageHtml);
            
            if (type === 'success') {
                setTimeout(() => {
                    $messages.fadeOut();
                }, 5000);
            }
        } else {
            // Fallback to alert
            alert(message);
        }
    }
    
    // Global function to manually enable button (for console testing)
    window.zkbEnableButton = enableButton;
    window.zkbTestEncrypt = handleEncrypt;
});
