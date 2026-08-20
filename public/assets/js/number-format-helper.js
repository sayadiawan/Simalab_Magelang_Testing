/**
 * Number Format Helper for Indonesian (ID) and International (EN) formats
 * 
 * ID Format: Thousands separator = . (dot), Decimal separator = , (comma)
 *            Example: 1.234,56
 * 
 * EN Format: Thousands separator = , (comma), Decimal separator = . (dot)
 *            Example: 1,234.56
 */

/**
 * Parse result that may be:
 * - numeric range: "0-1", "3 - 6"
 * - inequality: "<1", "> 5", "≤5", ">=10"
 * - single number: "2"
 *
 * Used for sediment-style results where abnormality is based on the upper bound.
 *
 * @param {string} value
 * @param {string} format - 'id' or 'en'
 * @returns {{low: number, high: number, isRange: boolean, operator: string|null, threshold: number|null}|null}
 */
function parseResultRange(value, format) {
    if (value === null || value === undefined || value === '' || value === '-') {
        return null;
    }

    var s = String(value).trim()
        .replace(/&lt;/gi, '<')
        .replace(/&gt;/gi, '>')
        .replace(/&le;/gi, '≤')
        .replace(/&ge;/gi, '≥')
        .replace(/\s+/g, ' ');

    // Inequality: <1, > 5, ≤5, >= 10, &lt; 3
    var opMatch = s.match(/^([<>≤≥]|<=|>=|≤|≥)\s*([\d.,]+)/);
    if (opMatch) {
        var rawOp = opMatch[1];
        var threshold = parseNumberInput(opMatch[2], format);
        if (threshold === null || isNaN(threshold)) {
            return null;
        }
        var operator = rawOp;
        if (rawOp === '≤' || rawOp === '<=') operator = '<=';
        if (rawOp === '≥' || rawOp === '>=') operator = '>=';
        return {
            low: threshold,
            high: threshold,
            isRange: true,
            operator: operator,
            threshold: threshold
        };
    }

    // Match "0-1", "0 - 1", "0,5-1,5" (not negative numbers like "-1")
    var rangeMatch = s.match(/^([\d.,]+)\s*-\s*([\d.,]+)/);
    if (rangeMatch) {
        var low = parseNumberInput(rangeMatch[1], format);
        var high = parseNumberInput(rangeMatch[2], format);
        if (low !== null && !isNaN(low) && high !== null && !isNaN(high)) {
            return { low: low, high: high, isRange: true, operator: null, threshold: null };
        }
    }

    var n = parseNumberInput(s, format);
    if (n !== null && !isNaN(n)) {
        return { low: n, high: n, isRange: false, operator: null, threshold: null };
    }

    return null;
}

/**
 * Compare hasil against min/max baku mutu.
 * - Range "a-b": normal hanya jika seluruh range di dalam [min, max]
 *   (abnormal jika a < min atau b > max)
 * - "< n" / "≤ n": abnormal jika seluruh nilai di bawah min, atau ambang > max
 * - "> n" / "≥ n": abnormal jika ambang >= max
 * - Scalar: standard min/max interval check
 *
 * @returns {boolean|null} true = melewati, false = normal, null = cannot evaluate
 */
function evaluateBakuMutuRange(value, min, max, format) {
    format = format || 'en';
    var parsed = parseResultRange(value, format);
    if (!parsed) {
        return null;
    }

    var hasMin = min !== undefined && min !== null && min !== '';
    var hasMax = max !== undefined && max !== null && max !== '';
    var minNum = hasMin ? parseNumberInput(min, format) : null;
    var maxNum = hasMax ? parseNumberInput(max, format) : null;

    // Inequality operators vs range baku mutu
    if (parsed.operator) {
        var t = parsed.threshold;
        if (parsed.operator === '<' || parsed.operator === '<=') {
            var belowMin = false;
            var aboveMax = false;
            if (hasMin && minNum !== null && !isNaN(minNum)) {
                belowMin = parsed.operator === '<' ? (t <= minNum) : (t < minNum);
            }
            if (hasMax && maxNum !== null && !isNaN(maxNum)) {
                // Ambang di atas max → himpunan bisa melebihi baku mutu
                aboveMax = t > maxNum;
            }
            if (!hasMin && !hasMax) {
                return null;
            }
            return belowMin || aboveMax;
        }
        if (parsed.operator === '>' || parsed.operator === '>=') {
            if (hasMax && maxNum !== null && !isNaN(maxNum)) {
                // >5 / ≥5 vs max=5 → abnormal (ambang sudah di/atas batas atas)
                // >4 vs max=5 → normal (ambang masih di dalam range)
                return t >= maxNum;
            }
            if (hasMin && minNum !== null && !isNaN(minNum)) {
                return false;
            }
            return null;
        }
    }

    // Numeric range "a-b": harus sepenuhnya di dalam [min, max]
    if (parsed.isRange) {
        if (hasMin && hasMax && minNum !== null && maxNum !== null && !isNaN(minNum) && !isNaN(maxNum)) {
            return (parsed.low < minNum || parsed.high > maxNum);
        }
        if (hasMax && maxNum !== null && !isNaN(maxNum)) {
            return parsed.high > maxNum;
        }
        if (hasMin && minNum !== null && !isNaN(minNum)) {
            return parsed.high < minNum || parsed.low < minNum;
        }
        return null;
    }

    if (hasMin && hasMax && minNum !== null && maxNum !== null) {
        return (parsed.low < minNum || parsed.low > maxNum);
    }
    if (hasMin && minNum !== null && !isNaN(minNum)) {
        return parsed.low < minNum;
    }
    if (hasMax && maxNum !== null && !isNaN(maxNum)) {
        return parsed.low > maxNum;
    }
    return null;
}

/**
 * Parse number string to float
 * @param {string} value - Number string in ID or EN format
 * @param {string} format - 'id' or 'en' (default: 'en')
 * @returns {number|null} - Parsed float or null if invalid
 */
function parseNumberInput(value, format) {
    if (!value || value === '' || value === '-') {
        return null;
    }

    // Convert to string and trim
    value = String(value).trim();
    
    // Remove all whitespace
    value = value.replace(/\s+/g, '');

    // If already a valid number (no separators), return it
    if (!isNaN(value) && !isNaN(parseFloat(value)) && !/[,.]/.test(value.slice(0, -1))) {
        return parseFloat(value);
    }

    format = format || 'en';

    try {
        console.log("format= ", format);
        var cleanValue;
        
        if (format === 'id') {
            // ID format: 1.234,56 -> 1234.56
            // Step 1: Remove ALL thousands separators (dot)
            cleanValue = value.replace(/\./g, '');
            // Step 2: Replace decimal separator (comma) with dot
            cleanValue = cleanValue.replace(/,/g, '.');
            // Step 3: Remove any remaining non-numeric except dot and minus
            cleanValue = cleanValue.replace(/[^\d.-]/g, '');
        } else {
            // EN format: 1,234.56 -> 1234.56
            // Step 1: Remove ALL thousands separators (comma)
            cleanValue = value.replace(/,/g, '');
            // Step 2: Remove any remaining non-numeric except dot and minus
            cleanValue = cleanValue.replace(/[^\d.-]/g, '');
        }
        
        var result = parseFloat(cleanValue);
        console.log("cleanValue= ", cleanValue, " result= ", result);
        return isNaN(result) ? null : result;
    } catch (e) {
        console.error('Error parsing number:', value, e);
        return null;
    }
}

/**
 * Format number for display
 * @param {number} value - Number to format
 * @param {string} format - 'id' or 'en' (default: 'en')
 * @param {number} decimals - Number of decimal places (default: auto-detect)
 * @returns {string} - Formatted number string
 */
function formatNumberDisplay(value, format, decimals) {
    if (value === null || value === undefined || value === '' || isNaN(value)) {
        return '';
    }

    value = parseFloat(value);
    if (isNaN(value)) {
        return '';
    }

    format = format || 'en';

    // Auto-detect decimal places if not specified
    if (decimals === undefined || decimals === null) {
        var valueStr = value.toString();
        if (valueStr.indexOf('.') !== -1) {
            decimals = valueStr.split('.')[1].length;
        } else {
            decimals = 0;
        }
    }

    // Format number with decimals
    var fixed = value.toFixed(decimals);
    var parts = fixed.split('.');
    var integerPart = parts[0];
    var decimalPart = parts[1];

    console.log("format= ", format);
    if (format === 'id') {
        // ID format: thousands = dot, decimal = comma
        // Add thousands separator
        integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        console.log("integerPart= ", integerPart);
        return decimalPart ? integerPart + ',' + decimalPart : integerPart;
    } else {
        // EN format: thousands = comma, decimal = dot
        // Add thousands separator
        integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        console.log("integerPart= ", integerPart);
        return decimalPart ? integerPart + '.' + decimalPart : integerPart;
    }
}

/**
 * Detect number format from string
 * @param {string} value - Number string
 * @returns {string} - 'id' or 'en'
 */
function detectNumberFormat(value) {
    if (!value || value === '' || value === '-') {
        return 'en';
    }

    value = String(value).trim();

    // Check for comma as decimal separator (ID format)
    // Pattern: digits, optional dots for thousands, comma for decimal
    if (/^\d{1,3}(\.\d{3})*(,\d+)?$/.test(value)) {
        return 'id';
    }

    // Check for dot as decimal separator (EN format)
    // Pattern: digits, optional commas for thousands, dot for decimal
    if (/^\d{1,3}(,\d{3})*(\.\d+)?$/.test(value)) {
        return 'en';
    }

    // If ambiguous, check last separator
    var lastComma = value.lastIndexOf(',');
    var lastDot = value.lastIndexOf('.');

    if (lastComma > lastDot) {
        // Last separator is comma, likely ID format
        return 'id';
    } else if (lastDot > lastComma) {
        // Last separator is dot, likely EN format
        return 'en';
    }

    // Default to EN
    return 'en';
}

/**
 * Parse number with automatic format detection
 * @param {string} value - Number string
 * @returns {number|null} - Parsed float or null if invalid
 */
function parseNumberAuto(value) {
    var format = detectNumberFormat(value);
    return parseNumberInput(value, format);
}

/**
 * Compare two numbers regardless of format
 * @param {string} value1 - First number string
 * @param {string} value2 - Second number string
 * @param {string} format - 'id' or 'en' (default: auto-detect)
 * @returns {number} - -1 if value1 < value2, 0 if equal, 1 if value1 > value2, null if invalid
 */
function compareNumbers(value1, value2, format) {
    var num1 = format ? parseNumberInput(value1, format) : parseNumberAuto(value1);
    var num2 = format ? parseNumberInput(value2, format) : parseNumberAuto(value2);

    if (num1 === null || num2 === null) {
        return null;
    }

    if (num1 < num2) return -1;
    if (num1 > num2) return 1;
    return 0;
}

// Make functions globally available
if (typeof window !== 'undefined') {
    window.parseNumberInput = parseNumberInput;
    window.parseResultRange = parseResultRange;
    window.evaluateBakuMutuRange = evaluateBakuMutuRange;
    window.formatNumberDisplay = formatNumberDisplay;
    window.detectNumberFormat = detectNumberFormat;
    window.parseNumberAuto = parseNumberAuto;
    window.compareNumbers = compareNumbers;
}

// Export for Node.js if available
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        parseNumberInput: parseNumberInput,
        parseResultRange: parseResultRange,
        evaluateBakuMutuRange: evaluateBakuMutuRange,
        formatNumberDisplay: formatNumberDisplay,
        detectNumberFormat: detectNumberFormat,
        parseNumberAuto: parseNumberAuto,
        compareNumbers: compareNumbers
    };
}

