const path = require('path')

module.exports = {
  input: ['src/**/*.{js,jsx,ts,tsx}', '!src/**/*.test.{js,jsx,ts,tsx}'],
  output: 'src/i18n/missing', // ← this is correct
  options: {
    debug: true,
    removeUnusedKeys: false,
    sort: true,
    func: { list: ['i18n.t', 't'], extensions: ['.js', '.jsx', '.ts', '.tsx'] },
    trans: {
      component: 'Trans',
      i18nKey: 'i18nKey',
      extensions: ['.js', '.jsx', '.ts', '.tsx'],
      defaultsKey: 'defaults',
      fallbackKey: false,
      supportBasicHtmlNodes: true,
      keepBasicHtmlNodesFor: ['br', 'strong', 'i', 'p'],
    },
    lngs: ['en', 'pl'],
    defaultLng: 'en',
    defaultNs: 'translation',
    keySeparator: false,
    nsSeparator: false,
    saveMissing: false,
    customValueTemplate: '',
    interpolation: {
      prefix: '{{',
      suffix: '}}',
    },
  },
}
