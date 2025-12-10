/**
 * ESLint configuration for vanilla JavaScript
 * 
 * This configuration enforces coding standards and catches common code quality issues.
 */

module.exports = {
    env: {
        browser: true,
        es2021: true,
        jquery: true,
    },
    extends: [
        'eslint:recommended',
    ],
    parserOptions: {
        ecmaVersion: 2021,
        sourceType: 'script', // Use 'script' for vanilla JS files
    },
    globals: {
        // Yii2 and jQuery globals
        'Yii': 'readonly',
        'jQuery': 'readonly',
        '$': 'readonly',
        // Chart.js
        'Chart': 'readonly',
        // D3
        'd3': 'readonly',
        // TinyMCE
        'tinymce': 'readonly',
        // CountUp
        'CountUp': 'readonly',
        // mxGraph
        'mxGraph': 'readonly',
        'mxClient': 'readonly',
        'mxUtils': 'readonly',
        'mxEvent': 'readonly',
        'mxCell': 'readonly',
        'mxGeometry': 'readonly',
        'mxGraphModel': 'readonly',
        'mxCodec': 'readonly',
        'mxEditor': 'readonly',
        // Bootstrap
        'bootstrap': 'readonly',
    },
    rules: {
        // Code quality rules to catch common issues
        
        // Best practices
        'no-console': 'warn', // Warn about console.log statements
        'no-debugger': 'error',
        'no-alert': 'warn',
        'no-eval': 'error',
        'no-implied-eval': 'error',
        'no-new-wrappers': 'error',
        'no-return-assign': 'error',
        'no-self-compare': 'error',
        'no-sequences': 'error',
        'no-throw-literal': 'error',
        'no-unmodified-loop-condition': 'error',
        'no-unused-expressions': 'error',
        'no-useless-call': 'error',
        'no-useless-concat': 'error',
        'no-useless-return': 'error',
        'no-void': 'error',
        'prefer-promise-reject-errors': 'error',
        'radix': 'error',
        'wrap-iife': ['error', 'any'],
        'yoda': 'error',
        
        // Variables
        'no-undef': 'error',
        'no-unused-vars': ['error', {
            'argsIgnorePattern': '^_',
            'varsIgnorePattern': '^_',
        }],
        'no-use-before-define': ['error', {
            'functions': false,
            'classes': false,
        }],
        
        // Stylistic issues
        'indent': ['error', 4, {
            'SwitchCase': 1,
            'VariableDeclarator': 1,
            'outerIIFEBody': 1,
            'MemberExpression': 1,
            'FunctionDeclaration': {
                'parameters': 1,
                'body': 1,
            },
            'FunctionExpression': {
                'parameters': 1,
                'body': 1,
            },
            'CallExpression': {
                'arguments': 1,
            },
            'ArrayExpression': 1,
            'ObjectExpression': 1,
            'ImportDeclaration': 1,
            'flatTernaryExpressions': false,
            'ignoreComments': false,
        }],
        'linebreak-style': ['error', 'unix'],
        'quotes': ['error', 'single', {
            'avoidEscape': true,
            'allowTemplateLiterals': true,
        }],
        'semi': ['error', 'always'],
        'comma-dangle': ['error', {
            'arrays': 'always-multiline',
            'objects': 'always-multiline',
            'imports': 'always-multiline',
            'exports': 'always-multiline',
            'functions': 'never',
        }],
        'comma-spacing': ['error', {
            'before': false,
            'after': true,
        }],
        'comma-style': ['error', 'last'],
        'computed-property-spacing': ['error', 'never'],
        'func-call-spacing': ['error', 'never'],
        'key-spacing': ['error', {
            'beforeColon': false,
            'afterColon': true,
        }],
        'keyword-spacing': ['error', {
            'before': true,
            'after': true,
        }],
        'no-multi-spaces': 'error',
        'no-trailing-spaces': 'error',
        'no-whitespace-before-property': 'error',
        'object-curly-spacing': ['error', 'always'],
        'padded-blocks': ['error', 'never'],
        'space-before-blocks': 'error',
        'space-before-function-paren': ['error', {
            'anonymous': 'always',
            'named': 'never',
            'asyncArrow': 'always',
        }],
        'space-in-parens': ['error', 'never'],
        'space-infix-ops': 'error',
        'space-unary-ops': ['error', {
            'words': true,
            'nonwords': false,
        }],
        'spaced-comment': ['error', 'always', {
            'line': {
                'markers': ['/'],
                'exceptions': ['-', '+'],
            },
            'block': {
                'markers': ['!'],
                'exceptions': ['*'],
                'balanced': true,
            },
        }],
        'arrow-spacing': ['error', {
            'before': true,
            'after': true,
        }],
        'block-spacing': ['error', 'always'],
        'brace-style': ['error', '1tbs', {
            'allowSingleLine': true,
        }],
        'curly': ['error', 'all'],
        'eol-last': ['error', 'always'],
        'max-len': ['warn', {
            'code': 120,
            'tabWidth': 4,
            'comments': 120,
            'ignoreComments': false,
            'ignoreTrailingComments': false,
            'ignoreUrls': true,
            'ignoreStrings': true,
            'ignoreTemplateLiterals': true,
            'ignoreRegExpLiterals': true,
        }],
        'no-multiple-empty-lines': ['error', {
            'max': 2,
            'maxEOF': 1,
            'maxBOF': 0,
        }],
        'no-tabs': 'error',
        
        // ES6 features (if used)
        'arrow-body-style': ['error', 'as-needed'],
        'arrow-parens': ['error', 'as-needed'],
        'no-var': 'warn', // Prefer let/const
        'prefer-const': 'warn',
        'prefer-arrow-callback': 'warn',
        'prefer-template': 'warn',
        
        // Common code issues
        'no-empty': 'error',
        'no-empty-function': 'warn',
        'no-extra-semi': 'error',
        'no-func-assign': 'error',
        'no-inner-declarations': 'error',
        'no-invalid-regexp': 'error',
        'no-irregular-whitespace': 'error',
        'no-obj-calls': 'error',
        'no-redeclare': 'error',
        'no-sparse-arrays': 'error',
        'no-unreachable': 'error',
        'use-isnan': 'error',
        'valid-typeof': 'error',
        
        // JSHint compatibility (for legacy code)
        'eqeqeq': ['error', 'always'],
        'no-caller': 'error',
        'no-extend-native': 'error',
        'no-iterator': 'error',
        'no-proto': 'error',
        'no-script-url': 'error',
        'no-shadow-restricted-names': 'error',
    },
    overrides: [
        {
            // Third-party libraries should be less strict
            files: ['web/js/third-party/**/*.js'],
            rules: {
                'no-undef': 'off',
                'no-unused-vars': 'off',
            },
        },
    ],
};

