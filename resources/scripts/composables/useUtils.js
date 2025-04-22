export default function useUtils() {
    const copyTextToClipboard = (text) => {
        if (navigator.clipboard && window.isSecureContext) {
            // Use the Clipboard API if available
            return navigator.clipboard.writeText(text);
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                textArea.remove();
                return Promise.resolve();
            } catch (err) {
                textArea.remove();
                return Promise.reject(err);
            }
        }
    };

    return {
        copyTextToClipboard
    };
} 