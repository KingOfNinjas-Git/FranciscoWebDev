// Helpers that send text from the admin editor in an encoded form.
//
// The web host's firewall (ModSecurity) rejects any form whose text looks like an attack, and a blog about web
// development is full of code examples that look exactly like that (getElementById, setTimeout, HTML tags...).
// The admin editor therefore sends those fields as base64 ("b64:...") inside a "_b64" group and the server decodes
// them (App\Http\Middleware\DecodeSafeFields). Only logged-in admin requests are decoded.

/** "hello" -> "b64:aGVsbG8=" (UTF-8 safe). */
export function toB64(text) {
    const bytes = new TextEncoder().encode(text);
    let binary = '';
    const chunk = 0x8000; // keep String.fromCharCode's argument list small for long posts
    for (let i = 0; i < bytes.length; i += chunk) {
        binary += String.fromCharCode.apply(null, bytes.subarray(i, i + chunk));
    }

    return 'b64:' + btoa(binary);
}

/** Moves the listed text fields of a JSON payload into the encoded "_b64" group. */
export function wrapPayload(payload, keys) {
    const plain = {};
    const encoded = {};

    for (const [key, value] of Object.entries(payload)) {
        if (keys.includes(key) && typeof value === 'string' && value !== '') {
            encoded[key] = toB64(value);
        } else {
            plain[key] = value;
        }
    }

    if (Object.keys(encoded).length) {
        plain._b64 = encoded;
    }

    return plain;
}

/** "translations[pt][body]" -> "_b64[translations][pt][body]" (the same nesting, under _b64). */
export function encodedFieldName(name) {
    const bracket = name.indexOf('[');

    return bracket === -1
        ? '_b64[' + name + ']'
        : '_b64[' + name.slice(0, bracket) + ']' + name.slice(bracket);
}
