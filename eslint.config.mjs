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
];
