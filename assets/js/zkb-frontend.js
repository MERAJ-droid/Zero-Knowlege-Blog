console.log('🚀 ZKB Frontend: Script loaded with smart quote fix!');

// Simple, reliable initialization with smart quote decoding
(function() {
    let initialized = false;
    
    // Smart quote decoder function
    function fixSmartQuotes(str) {
        if (typeof str !== 'string') return str;
        
        return str
            .replace(/[\u201C\u201D]/g, '"')  // Left and right double quotation marks
            .replace(/[\u2018\u2019]/g, "'")  // Left and right single quotation marks
            .replace(/[\u201E\u201F]/g, '"')  // Double low-9 quotation mark and others
            .replace(/[\u2039\u203A]/g, '"')  // Single left/right-pointing angle quotation marks
            .replace(/[\u00AB\u00BB]/g, '"'); // Left/right-pointing double angle quotation marks
    }
    
    function init() {
        if (initialized) return;
        
        console.log('🔧 ZKB Frontend: Initializing...');
        
        // Check dependencies
        if (typeof jQuery === 'undefined') {
            console.log('⏳ ZKB Frontend: Waiting for jQuery...');
            setTimeout(init, 100);
            return;
        }
        
        if (typeof ZKBCrypto === 'undefined') {
            console.log('⏳ ZKB Frontend: Waiting for ZKBCrypto...');
            setTimeout(init, 100);
            return;
        }
        
        const $ = jQuery;
        
        // Check for encrypted blocks
        const blocks = $('.zkb-encrypted-block');
        if (blocks.length === 0) {
            console.log('⏳ ZKB Frontend: No encrypted blocks found, retrying...');
            setTimeout(init, 500);
            return;
        }
        
        console.log('✅ ZKB Frontend: Found', blocks.length, 'encrypted blocks');
        
        // Bind events
        $(document).off('.zkb-frontend');
        
        $(document).on('click.zkb-frontend', '.zkb-decrypt-btn', function(e) {
            e.preventDefault();
            console.log('🔓 ZKB Frontend: Decrypt button clicked');
            handleDecrypt($(this));
        });
        
        $(document).on('keypress.zkb-frontend', '.zkb-password-input', function(e) {
            if (e.which === 13) {
                console.log('⌨️ ZKB Frontend: Enter key pressed');
                $(this).closest('.zkb-encrypted-block').find('.zkb-decrypt-btn').click();
            }
        });
        
        initialized = true;
        console.log('🎉 ZKB Frontend: Initialization complete!');
    }
    
    async function handleDecrypt(button) {
        const $ = jQuery;
        const container = button.closest('.zkb-encrypted-block');
        const passwordInput = container.find('.zkb-password-input');
        const password = passwordInput.val().trim();
        
        console.log('🔐 ZKB Frontend: Starting decryption process...');
        
        if (!password) {
            showMessage(container, '⚠️ Please enter a password', 'error');
            passwordInput.focus();
            return;
        }
        
        let encryptedData = passwordInput.data('encrypted');
        if (!encryptedData) {
            showMessage(container, '❌ No encrypted data found', 'error');
            return;
        }
        
        // IMPORTANT: Fix smart quotes that WordPress converts
        encryptedData = fixSmartQuotes(encryptedData);
        console.log('📊 ZKB Frontend: Smart quotes fixed, data length:', encryptedData.length);
        
        // Set loading state
        button.prop('disabled', true).text('🔄 Decrypting...');
        clearMessages(container);
        
        try {
            const contentData = JSON.parse(encryptedData);
            console.log('📋 ZKB Frontend: JSON parsed successfully');
            console.log('📋 ZKB Frontend: Keys:', Object.keys(contentData));
            
            const decryptedContent = await ZKBCrypto.decryptContent(contentData, password);
            console.log('✅ ZKB Frontend: Decryption successful!');
            
            displayContent(container, decryptedContent);
            passwordInput.val(''); // Clear password
            showMessage(container, '🎉 Content decrypted successfully!', 'success');
            
        } catch (error) {
            console.error('❌ ZKB Frontend: Decryption failed:', error);
            
            let errorMsg = '❌ Decryption failed';
            if (error.message.includes('Wrong password')) {
                errorMsg = '🔑 Wrong password. Please try again.';
            } else if (error.message.includes('JSON')) {
                errorMsg = '📄 Data format error';
            }
            
            showMessage(container, errorMsg, 'error');
            passwordInput.focus().select();
            
        } finally {
            button.prop('disabled', false).text('🔓 Decrypt Content');
        }
    }
    
    function displayContent(container, content) {
        const $ = jQuery;
        const decryptedDiv = container.find('.zkb-decrypted-content');
        const formDiv = container.find('.zkb-decrypt-form');
        
        const html = `
            <div style="background: #f8f9fa; padding: 25px; border-radius: 8px; border: 1px solid #dee2e6; margin-top: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #dee2e6;">
                    <h4 style="margin: 0; color: #28a745;">🔓 Decrypted Content</h4>
                    <button type="button" class="zkb-hide-btn" style="background: #6c757d; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 12px;">Hide</button>
                </div>
                <div class="zkb-content-body" style="background: white; padding: 20px; border-radius: 6px; line-height: 1.6; border: 1px solid #e9ecef; text-align: left;">
                    ${content}
                </div>
            </div>
        `;
        
        decryptedDiv.html(html);
        
        // Hide button functionality
        decryptedDiv.find('.zkb-hide-btn').on('click', function() {
            console.log('👁️ ZKB Frontend: Hiding content');
            decryptedDiv.hide().empty();
            formDiv.show();
            container.find('.zkb-password-input').focus();
            showMessage(container, 'Content hidden for security', 'info');
        });
        
        formDiv.hide();
        decryptedDiv.show();
        
        // Smooth scroll to content
        $('html, body').animate({
            scrollTop: container.offset().top - 50
        }, 500);
    }
    
    function showMessage(container, message, type) {
        const $ = jQuery;
        const colors = {
            success: '#d4edda',
            error: '#f8d7da',
            info: '#d1ecf1'
        };
        
        const bgColor = colors[type] || colors.info;
        
        const messagesDiv = container.find('.zkb-messages');
        messagesDiv.html(`
            <div style="padding: 12px 16px; margin: 15px 0; background: ${bgColor}; border-radius: 6px; font-size: 14px;">
                ${message}
            </div>
        `);
        
        if (type === 'success' || type === 'info') {
            setTimeout(() => clearMessages(container), 3000);
        }
    }
    
    function clearMessages(container) {
        container.find('.zkb-messages').empty();
    }
    
    // Start initialization
    init();
    
    // Also try when document is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    }
    
    // And when window loads
    window.addEventListener('load', init);
    
})();

console.log('📝 ZKB Frontend: Script setup complete with smart quote fix');
