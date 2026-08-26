export default [
  {
    ignores: ['node_modules/**', 'vendor/**', 'docs/**', 'releases/**', 'dist/**'],
  },
  {
    files: ['scripts/**/*.mjs', 'tests/**/*.js'],
    languageOptions: {
      globals: {
        console: 'readonly',
      },
    },
    rules: {
      'no-undef': 'error',
      'no-unused-vars': ['warn', { argsIgnorePattern: '^_' }],
    },
  },
  {
    files: ['plugin/restaurant-suite-core/assets/src/**/*.js'],
    languageOptions: {
      globals: {
        window: 'readonly',
        document: 'readonly',
        fetch: 'readonly',
        CustomEvent: 'readonly',
        FormData: 'readonly',
      },
    },
    rules: {
      'no-undef': 'error',
      'no-unused-vars': ['warn', { argsIgnorePattern: '^_' }],
    },
  },
];
