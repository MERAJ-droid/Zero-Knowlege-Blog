<?php
/**
 * Admin meta box template for post encryption
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="zkb-meta-box">
    <?php if ($is_encrypted): ?>
        <div class="zkb-status zkb-encrypted">
            <span class="dashicons dashicons-lock"></span>
            <strong>This post is encrypted</strong>
        </div>
        
        <div class="zkb-decrypt-section">
            <label for="zkb-admin-password">Enter password to decrypt:</label>
            <input type="password" id="zkb-admin-password" class="widefat" />
            <button type="button" id="zkb-decrypt-btn" class="button">Decrypt & Preview</button>
        </div>
        
        <div id="zkb-decrypted-preview" style="display: none;">
            <h4>Decrypted Content Preview:</h4>
            <div id="zkb-preview-title"></div>
            <div id="zkb-preview-content"></div>
            <button type="button" id="zkb-remove-encryption" class="button button-secondary">Remove Encryption</button>
        </div>
        
    <?php else: ?>
        <div class="zkb-status zkb-not-encrypted">
            <span class="dashicons dashicons-unlock"></span>
            <strong>This post is not encrypted</strong>
        </div>
        
        <div class="zkb-encrypt-section">
            <p>Encrypt this post with a password:</p>
            
            <label for="zkb-password">Password:</label>
            <input type="password" id="zkb-password" class="widefat" placeholder="Enter strong password" />
            
            <label for="zkb-password-confirm">Confirm Password:</label>
            <input type="password" id="zkb-password-confirm" class="widefat" placeholder="Confirm password" />
            
            <div class="zkb-options">
                <label>
                    <input type="checkbox" id="zkb-encrypt-title" />
                    Also encrypt the post title
                </label>
            </div>
            
            <div class="zkb-password-strength">
                <div class="zkb-strength-meter">
                    <div class="zkb-strength-fill"></div>
                </div>
                <div class="zkb-strength-text">Enter a password</div>
            </div>
            
            <button type="button" id="zkb-encrypt-btn" class="button button-primary" disabled>
                Encrypt Post
            </button>
        </div>
    <?php endif; ?>
    
    <div id="zkb-messages"></div>
    
    <div class="zkb-info">
        <p><small>
            <strong>Important:</strong> Once encrypted, you'll need the password to view or edit the content. 
            There is no password recovery option.
        </small></p>
    </div>
</div>

<input type="hidden" id="zkb-post-id" value="<?php echo esc_attr($post->ID); ?>" />
