<?php

namespace PtxDev\Phan;

use Phan\Issue;

class PhanConfig
{
    const BCMATH_SUPPRESS = [
        'argIndex' => [1, 2],
        'paramTypes' => ['int', 'float']
    ];

    private static $suppress = [
        'PhanTypeMismatchArgument' => [
            // request()->input 第二个参数可以为任何值
            '\Illuminate\Http\Request::input' => [
                'argIndex' => 2
            ],

            // join 第二个参数可以为Closure
            '\Illuminate\Database\Query\Builder::join' => [
                'argIndex' => 2,
                'paramTypes' => 'Closure'
            ],

            // groupBy 参数可以为字符串
            '\Illuminate\Database\Query\Builder::groupBy' => [
                'paramTypes' => 'string'
            ]
        ],


        'PhanTypeMismatchArgumentInternal' => [
            '\rand' => [
                'argIndex' => [1, 2],
                'paramTypes' => 'float'
            ],

            '\bcdiv' => self::BCMATH_SUPPRESS,
            '\bcmul' => self::BCMATH_SUPPRESS,
            '\bcmod' => self::BCMATH_SUPPRESS,
            '\bcadd' => self::BCMATH_SUPPRESS,
            '\bcsub' => self::BCMATH_SUPPRESS,
            '\bccomp' => self::BCMATH_SUPPRESS,
        ],

        'PhanUndeclaredStaticMethod' => [
            '\Illuminate\Support\Facades\Auth::user',
            '\Illuminate\Support\Facades\Auth::id'
        ],

        'PhanAccessNonStaticToStatic' => [
            'forceDelete'
        ]
    ];

    private static $directory_list = [
        'apps',
        'bootstrap',
        'config',
        'vendor/laravel',
        'vendor/illuminate',
        'vendor/psr/log',
        'vendor/symfony',
        'vendor/vlucas/phpdotenv',
        'vendor/ptx/lumen-require-dev/src',
        'vendor/ptx/lumen-require/src',
    ];

    private static $exclude_analysis_directory_list = [
        'vendor',
    ];

    public static function getConfig(array $configs = [])
    {
        $finalConfigs = static::getDefaultConfig();
        foreach ($configs as $key => $config) {
            $finalConfigs[$key] = static::mergeConfig($finalConfigs, $config, $key);
        }
        return $finalConfigs;
    }

    private static function mergeConfig($defualtConfig, $config, $key)
    {
        // 如果是数组则进行合并
        if (isset($defualtConfig[$key]) && is_array($defualtConfig[$key]) && is_array($config)) {
            if ($key === 'suppress' && count($config) > 0) {
                // suppress三级数组合并
                foreach ($config as $newKey => $newConfig) {
                    $config[$newKey] = static::mergeConfig($defualtConfig[$key], $newConfig, $newKey);
                }
            }
            $config = array_merge($defualtConfig[$key], $config);
        }
        return $config;
    }

    public static function getDefaultConfig()
    {
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        $processes = ($isWindows || !extension_loaded('pcntl')) ? 1 : 3;
        return [
            'suppress' => static::$suppress,
            'directory_list' => static::$directory_list,
            'exclude_analysis_directory_list' => static::$exclude_analysis_directory_list,

            // Supported values: '7.0', '7.1', '7.2', null.
            // If this is set to null,
            // then Phan assumes the PHP version which is closest to the minor version
            // of the php executable used to execute phan.
            // Automatically inferred from composer.json requirement for "php" of ">=7.1.3"
            'target_php_version' => '7.1',

            // If enabled, missing properties will be created when
            // they are first seen. If false, we'll report an
            // error message if there is an attempt to write
            // to a class property that wasn't explicitly
            // defined.
            'allow_missing_properties' => false,

            // If enabled, null can be cast as any type and any
            // type can be cast to null. Setting this to true
            // will cut down on false positives.
            'null_casts_as_any_type' => false,

            // If enabled, allow null to be cast as any array-like type.
            // This is an incremental step in migrating away from null_casts_as_any_type.
            // If null_casts_as_any_type is true, this has no effect.
            'null_casts_as_array' => true,

            // If enabled, allow any array-like type to be cast to null.
            // This is an incremental step in migrating away from null_casts_as_any_type.
            // If null_casts_as_any_type is true, this has no effect.
            'array_casts_as_null' => true,

            // If enabled, scalars (int, float, bool, string, null)
            // are treated as if they can cast to each other.
            // This does not affect checks of array keys. See scalar_array_key_cast.
            'scalar_implicit_cast' => false,

            // If enabled, any scalar array keys (int, string)
            // are treated as if they can cast to each other.
            // E.g. array<int,stdClass> can cast to array<string,stdClass> and vice versa.
            // Normally, a scalar type such as int could only cast to/from int and mixed.
            'scalar_array_key_cast' => true,

            // If this has entries, scalars (int, float, bool, string, null)
            // are allowed to perform the casts listed.
            // E.g. ['int' => ['float', 'string'], 'float' => ['int'], 'string' => ['int'], 'null' => ['string']]
            // allows casting null to a string, but not vice versa.
            // (subset of scalar_implicit_cast)
            'scalar_implicit_partial' => [],

            // If true, seemingly undeclared variables in the global
            // scope will be ignored. This is useful for projects
            // with complicated cross-file globals that you have no
            // hope of fixing.
            'ignore_undeclared_variables_in_global_scope' => true,

            // Set this to false to emit PhanUndeclaredFunction issues for internal functions that Phan has signatures for,
            // but aren't available in the codebase, or the internal functions used to run phan
            // (may lead to false positives if an extension isn't loaded)
            // If this is true(default), then Phan will not warn.
            'ignore_undeclared_functions_with_known_signatures' => true,

            // Backwards Compatibility Checking. This is slow
            // and expensive, but you should consider running
            // it before upgrading your version of PHP to a
            // new version that has backward compatibility
            // breaks.
            'backward_compatibility_checks' => false,

            // If true, check to make sure the return type declared
            // in the doc-block (if any) matches the return type
            // declared in the method signature.
            'check_docblock_signature_return_type_match' => false,

            // (*Requires check_docblock_signature_param_type_match to be true*)
            // If true, make narrowed types from phpdoc params override
            // the real types from the signature, when real types exist.
            // (E.g. allows specifying desired lists of subclasses,
            //  or to indicate a preference for non-nullable types over nullable types)
            // Affects analysis of the body of the method and the param types passed in by callers.
            'prefer_narrowed_phpdoc_param_type' => true,

            // (*Requires check_docblock_signature_return_type_match to be true*)
            // If true, make narrowed types from phpdoc returns override
            // the real types from the signature, when real types exist.
            // (E.g. allows specifying desired lists of subclasses,
            //  or to indicate a preference for non-nullable types over nullable types)
            // Affects analysis of return statements in the body of the method and the return types passed in by callers.
            'prefer_narrowed_phpdoc_return_type' => true,

            // If enabled, check all methods that override a
            // parent method to make sure its signature is
            // compatible with the parent's. This check
            // can add quite a bit of time to the analysis.
            // This will also check if final methods are overridden, etc.
            'analyze_signature_compatibility' => true,

            // This setting maps case insensitive strings to union types.
            // This is useful if a project uses phpdoc that differs from the phpdoc2 standard.
            // If the corresponding value is the empty string, Phan will ignore that union type
            // (E.g. can ignore 'the' in `@return the value`)
            // If the corresponding value is not empty, Phan will act as though it saw the corresponding unionTypes(s)
            // when the keys show up in a UnionType of @param, @return, @var, @property, etc.
            //
            // This matches the **entire string**, not parts of the string.
            // (E.g. `@return the|null` will still look for a class with the name `the`,
            // but `@return the` will be ignored with the below setting)
            //
            // (These are not aliases, this setting is ignored outside of doc comments).
            // (Phan does not check if classes with these names exist)
            //
            // Example setting: ['unknown' => '', 'number' => 'int|float', 'char' => 'string', 'long' => 'int', 'the' => '']
            'phpdoc_type_mapping' => [],

            // Set to true in order to attempt to detect dead
            // (unreferenced) code. Keep in mind that the
            // results will only be a guess given that classes,
            // properties, constants and methods can be referenced
            // as variables (like `$class->$property` or
            // `$class->$method()`) in ways that we're unable
            // to make sense of.
            'dead_code_detection' => false,

            // Set to true in order to attempt to detect unused variables.
            // dead_code_detection will also enable unused variable detection.
            // This has a few known false positives, e.g. for loops or branches.
            'unused_variable_detection' => false,

            // If true, this run a quick version of checks that takes less
            // time at the cost of not running as thorough
            // an analysis. You should consider setting this
            // to true only when you wish you had more **undiagnosed** issues
            // to fix in your code base.
            //
            // In quick-mode the scanner doesn't rescan a function
            // or a method's code block every time a call is seen.
            // This means that the problem here won't be detected:
            //
            // ```php
            // <?php
            // function test($arg):int {
            //     return $arg;
            // }
            // test("abc");
            // ```
            //
            // This would normally generate:
            //
            // ```sh
            // test.php:3 TypeError return string but `test()` is declared to return int
            // ```
            //
            // The initial scan of the function's code block has no
            // type information for `$arg`. It isn't until we see
            // the call and rescan test()'s code block that we can
            // detect that it is actually returning the passed in
            // `string` instead of an `int` as declared.
            'quick_mode' => false,

            // If true, then before analysis, try to simplify AST into a form
            // which improves Phan's type inference in edge cases.
            //
            // This may conflict with 'dead_code_detection'.
            // When this is true, this slows down analysis slightly.
            //
            // E.g. rewrites `if ($a = value() && $a > 0) {...}`
            // into $a = value(); if ($a) { if ($a > 0) {...}}`
            'simplify_ast' => false,

            // Enable or disable support for generic templated
            // class types.
            'generic_types_enabled' => true,

            // Override to hardcode existence and types of (non-builtin) globals in the global scope.
            // Class names should be prefixed with '\\'.
            // (E.g. ['_FOO' => '\\FooClass', 'page' => '\\PageClass', 'userId' => 'int'])
            'globals_type_map' => [],

            // The minimum severity level to report on. This can be
            // set to Issue::SEVERITY_LOW, Issue::SEVERITY_NORMAL or
            // Issue::SEVERITY_CRITICAL. Setting it to only
            // critical issues is a good place to start on a big
            // sloppy mature code base.
            'minimum_severity' => Issue::SEVERITY_LOW,

            // Add any issue types (such as 'PhanUndeclaredMethod')
            // to this black-list to inhibit them from being reported.
            'suppress_issue_types' => [],

            // A directory list that defines files that will be excluded
            // from static analysis, but whose class and method
            // information should be included.
            //
            // Generally, you'll want to include the directories for
            // third-party code (such as "vendor/") in this list.
            //
            // n.b.: If you'd like to parse but not analyze 3rd
            //       party code, directories containing that code
            //       should be added to the `directory_list` as
            //       to `excluce_analysis_directory_list`.

            // The number of processes to fork off during the analysis
            // phase.
            'processes' => $processes,

            'progress_bar' => true,

            // List of case-insensitive file extensions supported by Phan.
            // (e.g. php, html, htm)
            'analyzed_file_extensions' => [
                'php',
            ],

            // A list of plugin files to execute
            // Plugins which are bundled with Phan can be added here by providing their name (e.g. 'AlwaysReturnPlugin')
            // Alternately, you can pass in the full path to a PHP file with the plugin's implementation
            // (e.g. 'vendor/phan/phan/.phan/plugins/AlwaysReturnPlugin.php')
            'plugins' => [
                'AlwaysReturnPlugin',
                'PregRegexCheckerPlugin',
                'UnreachableCodePlugin',
                __DIR__ . '/Plugins/SuppressPlugin.php'
            ],

            'file_list' => [
            ],

            'exclude_file_list' => [
            ],

            'exclude_file_regex' => '@^(vendor|apps)/.*/(tests?|Tests?)/@',

            'autoload_internal_extension_signatures' => [
                'laravelIdeHelper' => '_ide_helper.php',
                'laravelMeta' => '.phpstorm.meta.php'
            ],
        ];
    }
}
