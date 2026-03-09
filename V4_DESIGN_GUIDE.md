# FranciscoWebDev — V4 Design System

## Overview
V4 is a premium, modern redesign emphasizing elegance, refined interactions, and exceptional mobile-first experience. The design features a sophisticated color palette, improved typography hierarchy, and seamless single-page mobile navigation.

---

## Design Philosophy
- **Mobile-First**: All pages combined into single scrollable experience on mobile (< 768px)
- **Desktop-Multipage**: Traditional multi-page navigation on larger screens
- **Refined Elegance**: Sophisticated color palette with strategic use of depth and contrast
- **Micro-interactions**: Smooth, purposeful animations that enhance UX
- **Typography-Driven**: Improved font hierarchy and spacing

---

## Color Palette

### Primary Gradient (Backgrounds)
- **Main**: `from-slate-50 via-blue-50 to-indigo-50`
  - Clean, minimal, premium feel
  
### Accent Gradient (CTAs & Highlights)
- **Primary CTA**: `from-indigo-600 via-purple-600 to-blue-600`
  - Hover: `from-indigo-700 via-purple-700 to-blue-700`
  - More saturated, professional feel

### Hero Section
- **Dark Overlay Gradient**: `from-slate-900/95 to-indigo-900/80`
  - High contrast for readability

### Card Backgrounds
- **Premium Card**: `bg-white` with `shadow-lg shadow-indigo-200/20`
  - Pure white with subtle shadow for depth
  
- **Accent Card**: `bg-gradient-to-br from-indigo-50 to-blue-50`
  - Subtle gradient for visual interest

### Text Colors
- **Headings**: `text-slate-900` (primary), `text-slate-800` (secondary)
- **Body Text**: `text-slate-700` (primary), `text-slate-600` (secondary)
- **Accents**: `text-indigo-600`, `text-purple-600`

### Supporting Colors
| Element | Color | Tailwind |
|---------|-------|----------|
| Success | `bg-emerald-50` + `text-emerald-700` | `emerald-*` |
| Error | `bg-red-50` + `text-red-700` | `red-*` |
| Info | `bg-blue-50` + `text-blue-700` | `blue-*` |
| Borders | `border-slate-200` | `slate-200` |
| Dividers | `bg-slate-200/50` | `slate-200/50` |

---

## Typography

### Font Stack
```css
font-family: 'Segoe UI', Trebuchet MS, sans-serif;
```

### Heading Hierarchy
- **H1 (Page Title)**: `text-5xl md:text-6xl font-bold text-slate-900` 
- **H2 (Section)**: `text-3xl md:text-4xl font-bold text-slate-900`
- **H3 (Subsection)**: `text-2xl font-semibold text-slate-900`
- **H4 (Item)**: `text-xl font-semibold text-slate-800`

### Body Text
- **Large**: `text-lg text-slate-700 leading-relaxed`
- **Regular**: `text-base text-slate-700 leading-relaxed`
- **Small**: `text-sm text-slate-600`

---

## Component Library

### 1. **Premium Card**
```html
<div class="bg-white rounded-2xl shadow-lg shadow-indigo-200/20 border border-slate-100 p-8 hover:shadow-xl hover:shadow-indigo-200/30 transition-shadow duration-300">
  <!-- Content -->
</div>
```

### 2. **Primary Button**
```html
<button class="bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 hover:from-indigo-700 hover:via-purple-700 hover:to-blue-700 text-white font-semibold py-3 px-8 rounded-xl shadow-lg shadow-indigo-300/40 hover:shadow-indigo-300/60 hover:-translate-y-0.5 transition-all duration-200">
  Button
</button>
```

### 3. **Secondary Button**
```html
<button class="bg-slate-100 hover:bg-slate-200 text-slate-900 font-semibold py-3 px-8 rounded-xl transition-colors duration-200">
  Button
</button>
```

### 4. **Hero Section**
```html
<section class="py-20 md:py-32 bg-gradient-to-b from-slate-900 to-indigo-900 relative overflow-hidden">
  <!-- Decorative elements -->
  <div class="absolute inset-0 opacity-10">
    <!-- Gradient orbs -->
  </div>
  <!-- Content with relative z-index -->
</section>
```

### 5. **Project/Portfolio Card**
```html
<div class="group bg-white rounded-2xl overflow-hidden shadow-lg shadow-indigo-200/20 hover:shadow-2xl hover:shadow-indigo-200/40 border border-slate-100 transition-all duration-300 hover:-translate-y-1">
  <div class="aspect-video bg-gradient-to-br from-indigo-100 to-blue-100 overflow-hidden">
    <img src="" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
  </div>
  <div class="p-6">
    <!-- Content -->
  </div>
</div>
```

### 6. **Icon with Background**
```html
<div class="w-12 h-12 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
  <i class="fas fa-icon"></i>
</div>
```

---

## Mobile Single-Page Layout

On mobile (< 768px):
- **Single scrollable page** combining Home + About + Projects + Contact
- **Fixed mobile navbar** with smooth scroll navigation
- **Anchor links** for internal navigation between sections
- **Section identifiers** for scroll position tracking

Structure:
```
1. Hero/Welcome Section (Home)
2. About Section 
3. Tech Stack
4. Projects Grid
5. Contact Section
6. Footer
```

---

## Spacing System
- **XS**: 4px (0.25rem)
- **SM**: 8px (0.5rem)
- **MD**: 16px (1rem)
- **LG**: 24px (1.5rem)
- **XL**: 32px (2rem)
- **2XL**: 48px (3rem)

---

## Animation Guidelines
- **Duration**: 200-300ms for interactions
- **Ease**: `ease-out-cubic` for smooth feel
- **Principles**: Subtle, purposeful, non-intrusive

### Common Animations
- Hover: `-translate-y-0.5` + shadow increase
- Hover Links: Smooth color transition
- Scroll: Fade-in + slide-up (AOS library)
- Page Transitions: Smooth fade

---

## Updated Color Tokens for V4

Replace from V2:
```
V2: from-indigo-400 via-blue-200 to-purple-100  
V4: from-slate-50 via-blue-50 to-indigo-50
```

```
V2: text-gray-300 / text-gray-400  
V4: text-slate-600 / text-slate-700
```

```
V2: from-cyan-300 via-blue-300 to-purple-300  
V4: from-indigo-600 via-purple-600 to-blue-600
```
