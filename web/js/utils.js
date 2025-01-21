/**
 * Debounce function to delay execution of a function
 * @param {Function} fn - The function to debounce
 * @param {number} delay - Delay in milliseconds
 * @returns {Function} - Debounced function
 */
function debounce(fn, delay) {
    let timeoutID;
    return function () {
        clearTimeout(timeoutID);
        const args = arguments;
        const that = this;
        timeoutID = setTimeout(function () {
            fn.apply(that, args);
        }, delay);
    };
}

// Export the debounce function if needed for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = debounce;
}
