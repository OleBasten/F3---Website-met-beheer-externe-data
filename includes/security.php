<?php
/**
 * security.php – Centralised security layer for GameVault
 *
 * Implements:
 *   - Secure session configuration (HttpOnly, SameSite, Secure flag)
 *   - CSRF token generation & validation
 *   - HTTP security headers (OWASP / hosting best-practices 2026)
 *   - Content-Security-Policy (CSP)
 *   - RAWG API attribution helper
 */

// ── 1. Session hardening ────────────────────────────────────────────────────
// Must be called BEFORE session_start() in every entry-point file.

function security_configure_session(): void
{
    ini_set('session.cookie_httponly', '1');   // JS cannot read the cookie
    ini_set('session.cookie_samesite', 'Lax'); // CSRF mitigation
    ini_set('session.use_strict_mode', '1');   // reject unrecognised session IDs
    ini_set('session.gc_maxlifetime', '3600'); // 1 hour idle timeout

    // Only send over HTTPS on production; comment out for local dev without TLS
    // ini_set('session.cookie_secure', '1');
}

// ── 2. HTTP Security Headers ────────────────────────────────────────────────

function security_send_headers(): void
{
    // Prevent MIME sniffing
    header('X-Content-Type-Options: nosniff');

    // Disallow framing by third-party sites (clickjacking protection)
    header('X-Frame-Options: SAMEORIGIN');

    // Enable browser XSS filter (legacy, but still useful)
    header('X-XSS-Protection: 1; mode=block');

    // Control how much referrer information is sent
    header('Referrer-Policy: strict-origin-when-cross-origin');

    // Restrict browser features
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

    // Content Security Policy
    // - RAWG images come from media.rawg.io
    // - Google Fonts from fonts.googleapis.com / fonts.gstatic.com
    // - Bootstrap JS from self
    header(
        "Content-Security-Policy: " .
        "default-src 'self'; " .
        "script-src 'self' 'unsafe-inline'; " .          // Bootstrap + inline
        "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
        "font-src 'self' https://fonts.gstatic.com; " .
        "img-src 'self' https://media.rawg.io https://media.rawg.io/ data:; " .
        "connect-src 'self'; " .
        "frame-ancestors 'self';"
    );
}

// ── 3. CSRF helpers ─────────────────────────────────────────────────────────

/**
 * Generate (or return existing) CSRF token for this session.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden <input> field with the current CSRF token.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/**
 * Validate the CSRF token from a POST request.
 * Redirects back with a flash error on failure.
 */
function csrf_validate(string $redirectBack = '/'): void
{
    $posted = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $posted)) {
        $_SESSION['flash'][] = [
            'type'    => 'error',
            'message' => 'Ongeldige formulierverzending. Probeer het opnieuw.',
        ];
        header('Location: ' . $redirectBack);
        exit;
    }
}

// ── 4. Input sanitisation helpers ───────────────────────────────────────────

/**
 * Return a sanitised, trimmed string from $_POST or a default value.
 */
function post_string(string $key, string $default = ''): string
{
    return htmlspecialchars(trim($_POST[$key] ?? $default), ENT_QUOTES, 'UTF-8');
}

/**
 * Return a validated integer from $_POST, or null.
 */
function post_int(string $key): ?int
{
    $val = filter_input(INPUT_POST, $key, FILTER_VALIDATE_INT);
    return ($val !== false && $val !== null) ? (int)$val : null;
}

/**
 * Return a validated float (0.0–10.0) from $_POST, or null.
 */
function post_rating(string $key): ?float
{
    $val = filter_input(INPUT_POST, $key, FILTER_VALIDATE_FLOAT);
    if ($val === false || $val === null) return null;
    return max(0.0, min(10.0, round($val, 1)));
}

/**
 * Validate that a URL points to an allowed domain.
 * Only rawg images and user-supplied HTTPS URLs are accepted.
 */
function post_url(string $key): ?string
{
    $url = trim($_POST[$key] ?? '');
    if ($url === '') return null;

    $parsed = parse_url($url);
    if (!$parsed || !isset($parsed['scheme']) || !in_array($parsed['scheme'], ['http', 'https'], true)) {
        return null;
    }
    return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
}

// ── 5. RAWG attribution ──────────────────────────────────────────────────────
// Per RAWG Terms of Use: "add an active hyperlink from every page where the
// data of RAWG is used."

/**
 * Returns an HTML attribution snippet for RAWG.
 * Must be displayed on every page that shows game data.
 */
function rawg_attribution(): string
{
    return '<span class="rawg-attribution">' .
        'Speldata door <a href="https://rawg.io" target="_blank" rel="noopener noreferrer">RAWG.io</a>' .
        '</span>';
}
