<?php
/**
 * Frontend decryption form template
 */

if (!defined('ABSPATH')) {
    exit;
}

global $post;
?>

<div class="zkb-decrypt-container" data-post-id="<?php echo esc_attr($post->ID); ?>">
    <div class="zkb-encrypted-notice">
        <div class="zkb-lock-icon">
            <span class="zkb-lock">🔒</span>
        </div>
        <h3>This content is encrypted</h3>
        <p>Enter the password to decrypt and view this content.</p>
    </div>
    
    <div class="zkb-decrypt-form">
        <div class="zkb-form-group">
            <label for="zkb-decrypt-password-<?php echo $post->ID; ?>">Password:</label>
            <input 
                type="password" 
                id="zkb-decrypt-password-<?php echo $post->ID; ?>" 
                class="zkb-password-input"
                placeholder="Enter password"
                autocomplete="off"
            />
        </div>
        
        <div class="zkb-form-actions">
            <button 
                type="button" 
                class="zkb-decrypt-btn"
                data-post-id="<?php echo esc_attr($post->ID); ?>"
            >
                <span class="zkb-btn-text">Decrypt Content</span>
                <span class="zkb-btn-loading" style="display: none;">Decrypting...</span>
            </button>
        </div>
    </div>
    
    <div class="zkb-messages" id="zkb-messages-<?php echo $post->ID; ?>"></div>
    
    <div class="zkb-decrypted-content" id="zkb-content-<?php echo $post->ID; ?>" style="display: none;">
        <div class="zkb-content-header">
            <div class="zkb-decrypted-title" id="zkb-title-<?php echo $post->ID; ?>"></div>
            <button type="button" class="zkb-hide-content" data-post-id="<?php echo $post->ID; ?>">
                Hide Content
            </button>
        </div>
        <div class="zkb-content-body" id="zkb-body-<?php echo $post->ID; ?>"></div>
    </div>
    
    <div class="zkb-security-notice">
        <p><small>
            <strong>Security Notice:</strong> Content is decrypted locally in your browser. 
            Your password is never sent to the server.
        </small></p>
    </div>
</div>
