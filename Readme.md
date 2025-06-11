# 🔐 Zero-Knowledge Blog – Privacy-First WordPress Encryption Plugin

**Zero-Knowledge Blog** is a WordPress plugin that empowers authors to encrypt blog content client-side using modern cryptography. Readers must enter a shared password to decrypt and view content — ensuring that **not even the server or site admin can read the post**.

This plugin enables secure, private blogging with a true zero-knowledge approach.

---

## ✨ Features

- 🔐 **Client-Side Encryption** – Content is encrypted in the browser with AES-256-GCM using the Web Crypto API
- 🔑 **Password-Protected Access** – Only users with the correct password can decrypt content
- 🛡️ **Zero-Knowledge Security** – Passwords and keys never leave the browser or reach the server
- 📦 **Encrypted Storage** – Posts are saved encrypted in the database; even admins can’t read them
- 🧩 **Seamless WordPress Integration** – Add encrypted content directly from the post editor
- 🖥️ **Frontend Decryption UI** – Readers are prompted for a password to reveal content
- ✅ **Smart Quote Handling** – Avoids WordPress auto-formatting issues with encrypted text
- 💡 **Responsive UI** – Works well across devices with clean admin/frontend design

---

## 🧩 How It Works

1. **Write a Post**  
   In the WordPress editor, enter your post content and set a password to encrypt it.

2. **Client-Side Encryption**  
   Your browser encrypts the post using AES-256-GCM before it's saved.

3. **Encrypted Storage**  
   The encrypted post is stored in the WordPress database — no plaintext is saved.

4. **Visitor Decryption**  
   Visitors see a decryption form. Only if they know the password, the content is decrypted **locally** in their browser.

---

## 🛠️ Technologies Used

- JavaScript Web Crypto API (AES-GCM encryption/decryption)
- PHP (WordPress plugin development)
- WordPress hooks and filters
- HTML/CSS for responsive UI
- DOM manipulation and async JS
- Bug handling for WordPress quote encoding

---

