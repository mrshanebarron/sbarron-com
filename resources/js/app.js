import './bootstrap'
// Self-hosted variable fonts — no FOUT, no external request.
import '@fontsource-variable/inter'
import '@fontsource-variable/fraunces'
import '@fontsource-variable/jetbrains-mono'
import { MotionPlugin } from '@vueuse/motion'
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { startHeatmapCapture } from './heatmap'

createInertiaApp({
  resolve: name => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
    return pages[`./Pages/${name}.vue`]
  },
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(MotionPlugin)
      .mount(el)
  },
  progress: { color: '#e8443b' },
})

// Admin heatmap — capture clicks + scroll depth on the public site.
startHeatmapCapture()
