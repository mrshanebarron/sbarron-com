<script setup>
/**
 * Ambient WebGL noise field. Pure GLSL, no scene graph, no Three.js.
 * Two-color (bone + oxblood), slow drift, respects prefers-reduced-motion.
 * Renders into a fixed-position canvas behind the hero, never blocks input.
 *
 * Performance: single quad, ~120 lines of shader, GPU-bound. Pause when off-screen.
 */
import { onMounted, onBeforeUnmount, ref } from 'vue'

const canvas = ref(null)
let gl, program, rafId, startTime
let mounted = true

const VERTEX = `
attribute vec2 a_position;
void main() { gl_Position = vec4(a_position, 0.0, 1.0); }
`

const FRAGMENT = `
precision highp float;
uniform vec2 u_resolution;
uniform float u_time;

// Classic 2D simplex noise via Ashima
vec3 mod289(vec3 x) { return x - floor(x * (1.0 / 289.0)) * 289.0; }
vec2 mod289(vec2 x) { return x - floor(x * (1.0 / 289.0)) * 289.0; }
vec3 permute(vec3 x) { return mod289(((x*34.0)+1.0)*x); }
float snoise(vec2 v) {
  const vec4 C = vec4(0.211324865405187, 0.366025403784439, -0.577350269189626, 0.024390243902439);
  vec2 i  = floor(v + dot(v, C.yy));
  vec2 x0 = v -   i + dot(i, C.xx);
  vec2 i1 = (x0.x > x0.y) ? vec2(1.0, 0.0) : vec2(0.0, 1.0);
  vec4 x12 = x0.xyxy + C.xxzz;
  x12.xy -= i1;
  i = mod289(i);
  vec3 p = permute(permute(i.y + vec3(0.0, i1.y, 1.0)) + i.x + vec3(0.0, i1.x, 1.0));
  vec3 m = max(0.5 - vec3(dot(x0,x0), dot(x12.xy,x12.xy), dot(x12.zw,x12.zw)), 0.0);
  m = m*m; m = m*m;
  vec3 x = 2.0 * fract(p * C.www) - 1.0;
  vec3 h = abs(x) - 0.5;
  vec3 ox = floor(x + 0.5);
  vec3 a0 = x - ox;
  m *= 1.79284291400159 - 0.85373472095314 * (a0*a0 + h*h);
  vec3 g;
  g.x  = a0.x  * x0.x  + h.x  * x0.y;
  g.yz = a0.yz * x12.xz + h.yz * x12.yw;
  return 130.0 * dot(m, g);
}

float fbm(vec2 p) {
  float v = 0.0;
  float a = 0.5;
  for (int i = 0; i < 4; i++) {
    v += a * snoise(p);
    p *= 2.0;
    a *= 0.5;
  }
  return v;
}

void main() {
  vec2 uv = gl_FragCoord.xy / u_resolution.xy;
  vec2 p = uv * 3.0;
  p.x *= u_resolution.x / u_resolution.y;
  float t = u_time * 0.04;

  // Two layered noise fields drifting at different speeds.
  float n = fbm(p + vec2(t, t * 0.7));
  float m = fbm(p * 1.6 + vec2(-t * 0.5, t * 0.3) + n * 0.5);

  // Domain warp for organic feel
  float warped = fbm(p + vec2(n, m) * 0.6 + t * 0.2);

  // Two-color blend: bone <-> oxblood, mostly bone with sparse oxblood.
  vec3 bone   = vec3(0.957, 0.933, 0.886);   // #f4eee2
  vec3 oxblood = vec3(0.541, 0.110, 0.110);  // #8a1c1c
  vec3 ink     = vec3(0.086, 0.078, 0.059);  // #16140f

  float mask = smoothstep(0.05, 0.45, warped);
  vec3 col = mix(bone, oxblood, mask * 0.18);
  col = mix(col, ink, smoothstep(0.85, 1.0, warped) * 0.08);

  // Vignette into bone at edges
  float vig = smoothstep(1.4, 0.4, length(uv - 0.5) * 1.3);
  col = mix(bone, col, vig * 0.55);

  gl_FragColor = vec4(col, 1.0);
}
`

function compileShader(type, src) {
  const s = gl.createShader(type)
  gl.shaderSource(s, src)
  gl.compileShader(s)
  if (!gl.getShaderParameter(s, gl.COMPILE_STATUS)) {
    console.warn('Shader compile error:', gl.getShaderInfoLog(s))
    return null
  }
  return s
}

function resize() {
  const dpr = Math.min(window.devicePixelRatio || 1, 1.5)
  const w = canvas.value.clientWidth * dpr
  const h = canvas.value.clientHeight * dpr
  if (canvas.value.width !== w || canvas.value.height !== h) {
    canvas.value.width = w
    canvas.value.height = h
    gl.viewport(0, 0, w, h)
  }
}

function render(now) {
  if (!mounted) return
  resize()
  gl.uniform2f(gl.getUniformLocation(program, 'u_resolution'), canvas.value.width, canvas.value.height)
  gl.uniform1f(gl.getUniformLocation(program, 'u_time'), (now - startTime) * 0.001)
  gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4)
  rafId = requestAnimationFrame(render)
}

onMounted(() => {
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches
  gl = canvas.value.getContext('webgl', { antialias: false, alpha: false })
  if (!gl) return // fail silent — page still works without backdrop

  const vs = compileShader(gl.VERTEX_SHADER, VERTEX)
  const fs = compileShader(gl.FRAGMENT_SHADER, FRAGMENT)
  if (!vs || !fs) return

  program = gl.createProgram()
  gl.attachShader(program, vs)
  gl.attachShader(program, fs)
  gl.linkProgram(program)
  gl.useProgram(program)

  const buf = gl.createBuffer()
  gl.bindBuffer(gl.ARRAY_BUFFER, buf)
  gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1,-1, 1,-1, -1,1, 1,1]), gl.STATIC_DRAW)
  const loc = gl.getAttribLocation(program, 'a_position')
  gl.enableVertexAttribArray(loc)
  gl.vertexAttribPointer(loc, 2, gl.FLOAT, false, 0, 0)

  startTime = performance.now()
  if (reduce) {
    // Render one static frame and stop
    resize()
    gl.uniform2f(gl.getUniformLocation(program, 'u_resolution'), canvas.value.width, canvas.value.height)
    gl.uniform1f(gl.getUniformLocation(program, 'u_time'), 0)
    gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4)
  } else {
    rafId = requestAnimationFrame(render)
  }
})

onBeforeUnmount(() => {
  mounted = false
  if (rafId) cancelAnimationFrame(rafId)
})
</script>

<template>
  <canvas
    ref="canvas"
    aria-hidden="true"
    style="position: absolute; inset: 0; width: 100%; height: 100%; display: block; pointer-events: none; z-index: 0;"
  ></canvas>
</template>
