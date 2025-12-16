<?php

/**
 * PHP CS Fixer configuration for Yii2 project.
 *
 * This configuration enforces coding standards and catches common code quality issues.
 */
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('vendor')
    ->exclude('runtime')
    ->exclude('web/assets')
    ->exclude('tests')
    ->exclude('migrations')
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRules([
        // Basic PSR-12 compliance
        '@PSR12' => true,

        // Yii2 specific rules
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'blank_line_after_namespace' => true,
        'blank_line_after_opening_tag' => true,
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw', 'try', 'if', 'switch', 'for', 'foreach', 'while'],
        ],

        // Code quality rules to catch common issues
        'no_empty_phpdoc' => true,
        'no_superfluous_phpdoc_tags' => [
            'allow_mixed' => true,
            'remove_inheritdoc' => true,
        ],
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_indent' => true,
        'phpdoc_inline_tag_normalizer' => true,
        'phpdoc_no_access' => true,
        'phpdoc_no_package' => true,
        'phpdoc_scalar' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_summary' => true,
        'phpdoc_to_comment' => true,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'phpdoc_var_without_name' => true,

        // Prevent common code issues
        'no_empty_statement' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'curly_brace_block',
                'extra',
                'parenthesis_brace_block',
                'square_brace_block',
                'throw',
                'use',
            ],
        ],
        'no_trailing_whitespace' => true,
        'no_trailing_whitespace_in_comment' => true,
        'no_whitespace_in_blank_line' => true,
        'single_blank_line_at_eof' => true,
        'single_blank_line_before_namespace' => true,
        'no_closing_tag' => true,
        'no_leading_import_slash' => true,
        'no_leading_namespace_whitespace' => true,
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_spaces_around_offset' => true,
        'no_spaces_inside_parenthesis' => true,
        'no_trailing_comma_in_list_call' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_unneeded_control_parentheses' => true,
        'no_unneeded_curly_braces' => true,
        'no_whitespace_before_comma_in_array' => true,
        'normalize_index_brace' => true,
        'trim_array_spaces' => true,
        'whitespace_after_comma_in_array' => true,

        // Function and method rules
        'function_typehint_space' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'no_spaces_after_function_name' => true,
        'return_type_declaration' => true,

        // Control structures
        'control_structure_continuation_position' => true,
        'elseif' => true,
        'no_break_comment' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'switch_case_semicolon_to_colon' => true,
        'switch_case_space' => true,

        // Operators
        'binary_operator_spaces' => [
            'default' => 'single_space',
            'operators' => [
                '=' => 'single_space',
                '=>' => 'single_space',
            ],
        ],
        'concat_space' => ['spacing' => 'one'],
        'increment_style' => ['style' => 'post'],
        'not_operator_with_successor_space' => true,
        'object_operator_without_whitespace' => true,
        'operator_linebreak' => [
            'only_booleans' => true,
            'position' => 'end',
        ],
        'standardize_increment' => true,
        'standardize_not_equals' => true,
        'ternary_operator_spaces' => true,
        'ternary_to_null_coalescing' => true,
        'unary_operator_spaces' => true,

        // Casting
        'cast_spaces' => true,
        'lowercase_cast' => true,
        'short_scalar_cast' => true,

        // Class and object rules
        'class_attributes_separation' => [
            'elements' => [
                'method' => 'one',
                'property' => 'one',
            ],
        ],
        'self_accessor' => true,
        'visibility_required' => [
            'elements' => ['method', 'property'],
        ],

        // String rules
        'single_quote' => true,
        'explicit_string_variable' => true,

        // General formatting
        // Note: 'braces' rule is deprecated in favor of 'braces_position' (coming in v4.0)
        // For now, using 'braces' as it's still available in v3.4.0
        'braces' => [
            'allow_single_line_anonymous_class_with_empty_body' => true,
            'allow_single_line_closure' => true,
            'position_after_control_structures' => 'same',
            'position_after_functions_and_oop_constructs' => 'same',
        ],
        'indentation_type' => true,
        'line_ending' => true,
        'linebreak_after_opening_tag' => true,
        'lowercase_keywords' => true,
        'lowercase_static_reference' => true,
        'magic_constant_casing' => true,
        'magic_method_casing' => true,
        'method_chaining_indentation' => true,
        'native_function_casing' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_mixed_echo_print' => ['use' => 'echo'],
        'no_short_bool_cast' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_unneeded_final_method' => true,
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public',
                'method_protected',
                'method_private',
            ],
        ],
        'single_class_element_per_statement' => true,
        'single_line_comment_style' => ['comment_types' => ['hash']],
        'single_line_throw' => false,
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setUsingCache(true)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache');
