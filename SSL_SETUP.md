# SSL Certificate Setup Guide

This guide covers SSL certificate installation and HTTPS configuration for the 3D Print Platform on shared hosting.

## Table of Contents

1. [Let's Encrypt SSL (Free)](#lets-encrypt-ssl-free)
2. [Commercial SSL Certificate](#commercial-ssl-certificate)
3. [Enable HTTPS Redirect](#enable-https-redirect)
4. [Verify SSL Installation](#verify-ssl-installation)
5. [Troubleshooting](#troubleshooting)

---

## Let's Encrypt SSL (Free)

Most shared hosting providers (especially cPanel) offer free Let's Encrypt SSL certificates with automatic renewal.

### Via cPanel

#### Step 1: Access SSL/TLS Status

1. Log into cPanel
2. Navigate to **Security** section
3. Click **SSL/TLS Status**

#### Step 2: Install Certificate

1. Find your domain in the list
2. Check the box next to your domain
3. Click **Run AutoSSL**
4. Wait for the process to complete (usually 1-5 minutes)

You should see:
```
✓ example.com - Certificate installed successfully
```

#### Step 3: Verify Installation

Visit your site with HTTPS: `https://yourdomain.com`

If the certificate is installed correctly:
- ✅ No certificate warnings
- ✅ Padlock icon in browser
- ✅ Certificate valid for 90 days

### Via SSH (Advanced)

If you have SSH access and Certbot installed:

```bash
# Install Certbot (if not installed)
sudo apt-get update
sudo apt-get install certbot

# Generate certificate
sudo certbot certonly --webroot -w /home/c/ch167436/3dPrint/public_html -d yourdomain.com -d www.yourdomain.com

# Certificate will be saved to:
# /etc/letsencrypt/live/yourdomain.com/
```

**Note:** On shared hosting, you may need to request your hosting provider to enable Certbot or use cPanel's AutoSSL instead.

### Automatic Renewal

Let's Encrypt certificates are valid for 90 days. With cPanel's AutoSSL, renewal is automatic.

To verify auto-renewal is working:

1. cPanel → SSL/TLS Status
2. Check "Auto-Renewal" column shows "✓ Enabled"

---

## Commercial SSL Certificate

If you purchased an SSL certificate from a provider (Comodo, DigiCert, GoDaddy, etc.):

### Step 1: Generate CSR (Certificate Signing Request)

#### Via cPanel

1. cPanel → **Security** → **SSL/TLS**
2. Click **Generate, view, or delete SSL certificate signing requests**
3. Fill in domain information:
   - **Domains**: yourdomain.com
   - **City/Locality**: Your City
   - **State/Province**: Your State
   - **Country**: Your Country (2-letter code)
   - **Company**: Your Company
   - **Company Division**: IT or Web
   - **Email**: admin@yourdomain.com
   - **Key Size**: 2048 (minimum, 4096 recommended)
4. Click **Generate**
5. Copy the CSR (starts with `-----BEGIN CERTIFICATE REQUEST-----`)

#### Via SSH

```bash
# Generate private key
openssl genrsa -out yourdomain.com.key 2048

# Generate CSR
openssl req -new -key yourdomain.com.key -out yourdomain.com.csr

# Follow prompts to enter domain information
```

### Step 2: Submit CSR to Certificate Authority

1. Go to your SSL provider's website
2. Submit the CSR you generated
3. Complete domain verification (email, DNS, or file upload)
4. Wait for certificate issuance (can take hours to days)
5. Download the certificate files:
   - Certificate (CRT)
   - Private Key (KEY)
   - CA Bundle (intermediate certificates)

### Step 3: Install Certificate

#### Via cPanel

1. cPanel → **Security** → **SSL/TLS**
2. Click **Manage SSL sites**
3. Select your domain
4. Paste certificate files:
   - **Certificate (CRT)**: Paste your certificate
   - **Private Key (KEY)**: Paste your private key
   - **Certificate Authority Bundle**: Paste CA bundle
5. Click **Install Certificate**

#### Via SSH (if you have access to Apache config)

```bash
# Copy certificate files to Apache SSL directory
sudo cp yourdomain.com.crt /etc/ssl/certs/
sudo cp yourdomain.com.key /etc/ssl/private/
sudo cp ca_bundle.crt /etc/ssl/certs/

# Edit Apache SSL configuration
sudo nano /etc/apache2/sites-available/yourdomain-ssl.conf

# Add these lines:
SSLCertificateFile /etc/ssl/certs/yourdomain.com.crt
SSLCertificateKeyFile /etc/ssl/private/yourdomain.com.key
SSLCertificateChainFile /etc/ssl/certs/ca_bundle.crt

# Enable SSL site
sudo a2ensite yourdomain-ssl.conf
sudo systemctl restart apache2
```

---

## Enable HTTPS Redirect

Once SSL is active, force all traffic to use HTTPS.

### Step 1: Test HTTPS

First, verify HTTPS works:
```
https://yourdomain.com
```

✅ If it loads without warnings, proceed to enable redirect.

### Step 2: Enable Redirect in .htaccess

Edit three .htaccess files and uncomment the HTTPS redirect sections:

#### `public_html/.htaccess`

Find these lines (around line 82-87):

```apache
# Redirect to HTTPS (uncomment when SSL certificate is active)
# <IfModule mod_rewrite.c>
#     RewriteEngine On
#     RewriteCond %{HTTPS} off
#     RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
# </IfModule>
```

**Uncomment** to:

```apache
# Redirect to HTTPS
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
</IfModule>
```

#### `api/.htaccess`

Find and uncomment (around line 7-9):

```apache
# Redirect to HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
```

#### `admin/.htaccess`

Same as above - uncomment the HTTPS redirect.

### Step 3: Enable HSTS (Recommended)

HSTS (HTTP Strict Transport Security) forces browsers to always use HTTPS.

**Only enable AFTER confirming HTTPS works perfectly!**

Edit the same three .htaccess files and uncomment HSTS:

#### `public_html/.htaccess`

Around line 89-93:

```apache
# HSTS - HTTP Strict Transport Security
<IfModule mod_headers.c>
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" env=HTTPS
</IfModule>
```

#### `api/.htaccess` and `admin/.htaccess`

Around line 22:

```apache
# HSTS
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains" env=HTTPS
```

### Step 4: Test Redirect

1. Visit: `http://yourdomain.com` (HTTP, not HTTPS)
2. You should be automatically redirected to: `https://yourdomain.com`
3. Check the URL bar - it should show `https://` and a padlock icon

---

## Verify SSL Installation

### 1. SSL Labs Test

Visit: https://www.ssllabs.com/ssltest/

1. Enter your domain
2. Click **Submit**
3. Wait for the scan to complete (2-3 minutes)

**Goal:** Grade A or A+

**If grade is lower:**
- Check TLS version support
- Verify certificate chain is complete
- Review cipher suite configuration

### 2. Manual Browser Check

1. Visit: `https://yourdomain.com`
2. Click the padlock icon in the address bar
3. Click "Certificate" or "Connection is secure"
4. Verify:
   - ✅ Certificate is valid
   - ✅ Issued to correct domain
   - ✅ Not expired
   - ✅ Issued by trusted CA

### 3. Command Line Check

```bash
# Check certificate details
openssl s_client -connect yourdomain.com:443 -servername yourdomain.com

# Check if HTTPS redirect works
curl -I http://yourdomain.com
# Should return: HTTP/1.1 301 Moved Permanently
# Location: https://yourdomain.com/

# Check security headers
curl -I https://yourdomain.com
# Should include:
# Strict-Transport-Security: max-age=31536000; includeSubDomains; preload
# X-Content-Type-Options: nosniff
# X-Frame-Options: SAMEORIGIN
```

### 4. Security Headers Check

Visit: https://securityheaders.com/

Enter your domain and check the grade.

**Expected headers:**
- ✅ Strict-Transport-Security (HSTS)
- ✅ X-Content-Type-Options
- ✅ X-Frame-Options
- ✅ X-XSS-Protection
- ✅ Content-Security-Policy
- ✅ Referrer-Policy

---

## Troubleshooting

### Issue: Certificate Not Trusted

**Symptoms:**
- Browser shows "Not Secure" or certificate warning
- "Certificate not trusted" error

**Solutions:**

1. **Check certificate chain**:
   ```bash
   openssl s_client -connect yourdomain.com:443 -showcerts
   ```
   Verify all intermediate certificates are present.

2. **Install CA bundle**: Ensure you installed the CA bundle (intermediate certificates).

3. **Clear browser cache**: Sometimes browsers cache old certificates.
   - Chrome: Ctrl+Shift+Delete → Clear cached images and files
   - Firefox: Ctrl+Shift+Delete → Clear cache

4. **Check certificate expiration**:
   ```bash
   openssl s_client -connect yourdomain.com:443 | openssl x509 -noout -dates
   ```

### Issue: Mixed Content Warnings

**Symptoms:**
- Padlock icon with warning
- Console errors: "Mixed Content: The page was loaded over HTTPS, but..."

**Solutions:**

1. **Find mixed content**:
   - Open browser console (F12)
   - Look for HTTP resources on HTTPS page
   - Common culprits: images, scripts, stylesheets

2. **Fix URLs**: Change `http://` to `https://` or use protocol-relative URLs `//`:
   ```html
   <!-- Bad -->
   <img src="http://example.com/image.jpg">
   
   <!-- Good -->
   <img src="https://example.com/image.jpg">
   
   <!-- Also good (protocol-relative) -->
   <img src="//example.com/image.jpg">
   ```

3. **Update .env**: Ensure `SITE_URL` uses HTTPS:
   ```env
   SITE_URL=https://yourdomain.com
   ```

### Issue: Redirect Loop

**Symptoms:**
- Browser says "Too many redirects"
- Site keeps redirecting indefinitely

**Solutions:**

1. **Check Apache configuration**: Some servers use different methods to detect HTTPS.

   Try alternative redirect rules in `.htaccess`:

   ```apache
   # Method 1 (standard)
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
   
   # Method 2 (some shared hosts)
   RewriteCond %{HTTP:X-Forwarded-Proto} !https
   RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
   
   # Method 3 (cloudflare and some proxies)
   RewriteCond %{HTTP:CF-Visitor} '"scheme":"http"'
   RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
   ```

2. **Disable plugins/middleware**: Temporarily disable any redirect plugins or middleware.

3. **Contact hosting support**: Some shared hosts have server-level SSL redirects that conflict.

### Issue: HSTS Too Strict

**Symptoms:**
- Can't access site even after disabling HTTPS
- Browser refuses to connect

**Solution:**

HSTS tells browsers to ONLY use HTTPS. Once enabled, browsers cache this for the `max-age` period (1 year).

1. **Temporary fix**: Use incognito/private browsing mode (doesn't have HSTS cache).

2. **Clear HSTS cache**:
   - **Chrome**: Visit `chrome://net-internals/#hsts`
     - Domain Security Policy → Delete domain
   - **Firefox**: Clear browsing history and site preferences

3. **Reduce max-age**: Edit `.htaccess`:
   ```apache
   # Start with shorter period for testing
   Header always set Strict-Transport-Security "max-age=300" env=HTTPS
   
   # After confirming everything works, increase to 1 year
   Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" env=HTTPS
   ```

### Issue: Certificate Mismatch

**Symptoms:**
- Certificate is for different domain
- "Certificate name mismatch" error

**Solutions:**

1. **Check certificate**: Verify certificate includes your domain:
   ```bash
   openssl s_client -connect yourdomain.com:443 | openssl x509 -noout -text | grep DNS
   ```

2. **Use wildcard certificate**: If using subdomains, ensure certificate covers them:
   - `*.example.com` covers `www.example.com`, `api.example.com`, etc.

3. **Reissue certificate**: If domain changed, generate new certificate for correct domain.

---

## Best Practices

### 1. Always Use HTTPS

- Never serve any content over HTTP in production
- Enable HTTPS redirect immediately after SSL installation
- Use HSTS to prevent downgrade attacks

### 2. Use Strong Configuration

- TLS 1.2 and 1.3 only (disable TLS 1.0 and 1.1)
- Strong cipher suites
- Enable Forward Secrecy

Most shared hosting providers configure this automatically, but verify with SSL Labs test.

### 3. Monitor Certificate Expiration

- Let's Encrypt: 90 days (auto-renews)
- Commercial: Usually 1-2 years

Set reminders 30 days before expiration to verify renewal.

### 4. Keep Certificates Updated

- Renew before expiration
- Update to stronger algorithms when available
- Monitor for CA policy changes

### 5. Use HSTS Preload (Optional)

For maximum security, submit to HSTS preload list: https://hstspreload.org/

**Requirements:**
- Valid certificate
- HTTPS redirect active
- HSTS with `max-age` ≥ 1 year
- Include `includeSubDomains` and `preload` directives

---

## Quick Reference

### Enable HTTPS Redirect

```apache
# In .htaccess
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}/$1 [R=301,L]
</IfModule>
```

### Enable HSTS

```apache
# In .htaccess
<IfModule mod_headers.c>
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload" env=HTTPS
</IfModule>
```

### Test Commands

```bash
# Check certificate
openssl s_client -connect yourdomain.com:443

# Test redirect
curl -I http://yourdomain.com

# Check security headers
curl -I https://yourdomain.com

# Verify SSL grade
# Visit: https://www.ssllabs.com/ssltest/
```

---

## Next Steps

After SSL is configured:

1. ✅ Run SSL Labs test (aim for A or A+)
2. ✅ Test all pages with HTTPS
3. ✅ Update sitemap URLs to HTTPS
4. ✅ Submit updated sitemap to Google Search Console
5. ✅ Update social media links to HTTPS
6. ✅ Monitor for mixed content warnings
7. ✅ Set up certificate expiration monitoring

---

**SSL Setup Complete!** 🔒

Your site is now secured with HTTPS. Continue with [LAUNCH_CHECKLIST.md](LAUNCH_CHECKLIST.md) for complete deployment verification.
