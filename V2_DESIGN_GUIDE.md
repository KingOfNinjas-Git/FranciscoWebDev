# FranciscoWebDev — V2 Design System & Completion Checklist

## Overview
The V2 redesign is a modern, gradient-based visual system using soft indigo, cyan, and emerald accents. It emphasizes clean cards, smooth animations, and accessible contrast.

---

## Color Palette

### Primary Gradients
- **Main Theme**: `from-indigo-400 via-cyan-300 to-purple-300`
  - Used for page backgrounds
  
- **Accent Gradient (Buttons)**: `from-indigo-500 via-cyan-500 to-emerald-500`
  - Hover state: `from-indigo-600 via-cyan-600 to-emerald-600`
  - Used for CTAs and interactive elements

- **Header Gradient (Admin)**: `from-indigo-600 via-cyan-600 to-emerald-600`
  - Dark, professional header for admin sections

- **Card Gradient**: `from-indigo-50 via-blue-50 to-cyan-50`
  - Light, subtle background for card containers

### Supporting Colors
| Use Case | Color | Tailwind |
|----------|-------|----------|
| Success Messages | `bg-emerald-100` + `text-emerald-800` | `emerald-*` |
| Error Messages | `bg-red-100` + `text-red-800` | `red-*` |
| Borders | `border-white/80` | `white/80` |
| Text (Dark) | `text-indigo-900` | `indigo-900` |
| Text (Light) | `text-gray-700` | `gray-700` |
| Shadows | `shadow-indigo-400/40` | `shadow-*` with opacity |

---

## Component Library

### 1. **Cards** (`.v2-card` equivalent)
Standard card containers with soft gradients.

```html
<div class="bg-gradient-to-b from-indigo-50 via-blue-50 to-cyan-50 rounded-2xl shadow-lg shadow-indigo-200/50 border border-white/80 p-6 backdrop-blur-sm">
  <!-- Content -->
</div>
```

**Variants:**
- **Light Card**: `from-indigo-50 via-blue-50 to-cyan-50` (standard)
- **White Card**: `from-white/95 via-indigo-50/90 to-cyan-50/90` (login form)
- **Accent Card**: `from-cyan-50/80 via-white/90 to-indigo-50/70` (upload form)

### 2. **Buttons** (`.v2-btn` equivalent)
Primary action buttons with gradient and hover effects.

```html
<button class="bg-gradient-to-r from-indigo-500 via-cyan-500 to-emerald-500 hover:from-indigo-600 hover:via-cyan-600 hover:to-emerald-600 text-white font-semibold py-3 rounded-xl shadow-lg shadow-indigo-400/40 hover:shadow-indigo-400/60 hover:-translate-y-0.5 transition duration-200">
  Action Button
</button>
```

**States:**
- **Default**: Standard gradient
- **Hover**: Darker gradient + lift effect (`-translate-y-0.5`) + enhanced shadow
- **Focus**: Keyboard navigation supported via `:focus` (add as needed)

**Variants:**
- **Full Width**: Add `w-full`
- **Smaller**: `py-2` + `px-4` instead of `py-3`
- **Ghost**: `bg-white/20 backdrop-blur-md` (navbar logout)

### 3. **Hero Sections**
Large, statement-making backgrounds.

```html
<main class="bg-gradient-to-b from-indigo-200/40 via-cyan-100/30 to-transparent flex flex-col items-center justify-center py-16">
  <!-- Hero Content -->
</main>
```

**Used on:** Home, About pages

### 4. **Project Cards** (Grid)
Individual project tiles in the projects list.

```html
<div class="bg-gradient-to-b from-indigo-50 via-blue-50 to-cyan-50 rounded-2xl shadow-lg shadow-indigo-200/50 border border-white/80 p-6 flex flex-col transition duration-300 hover:scale-105 hover:shadow-indigo-300/50 animate-fade-in-up project-card backdrop-blur-sm">
  <!-- Project content -->
</div>
```

**Interactions:**
- Hover: Scale up (`hover:scale-105`)
- Shadow: Enhanced on hover
- Animation: Staggered fade-in on load

### 5. **Admin Project Cards**
Admin list view with image overlay and badges.

```html
<div class="group rounded-2xl overflow-hidden bg-gradient-to-b from-indigo-50 via-white to-cyan-50/80 shadow-lg hover:shadow-2xl hover:shadow-indigo-300/40 border border-white/80 transition duration-300 hover:-translate-y-1 backdrop-blur-sm">
  <!-- Image with badge -->
  <div class="relative h-48 bg-gradient-to-br from-indigo-400 to-cyan-400 overflow-hidden">
    <!-- Image -->
    <div class="absolute top-3 right-3 bg-indigo-600 text-white text-xs font-bold px-3 py-1 rounded-full">
      {{ count }} img
    </div>
  </div>
</div>
```

### 6. **Form Inputs**
Consistent styling for form fields.

```html
<input type="text" class="w-full px-4 py-3 rounded-xl border-2 border-indigo-200 focus:border-indigo-500 focus:outline-none shadow-sm bg-white text-gray-900 transition">
```

**States:**
- **Default**: `border-indigo-200`
- **Focus**: `border-indigo-500` + `outline-none`
- **Textarea**: Add `resize-none` to prevent user resizing

### 7. **Alert Boxes**
Contextual feedback messages.

```html
<!-- Success -->
<div class="p-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 rounded-lg shadow-md">
  ✓ Success message
</div>

<!-- Error -->
<div class="p-4 bg-red-100 border-l-4 border-red-500 text-red-800 rounded-lg shadow-md">
  ⚠ Error message
</div>
```

---

## Typography

| Element | Style | Tailwind |
|---------|-------|----------|
| Page Title | Extra Large, Bold | `text-4xl font-extrabold` |
| Section Title | Large, Bold | `text-2xl font-extrabold` |
| Card Title | Medium, Bold | `text-xl font-extrabold` |
| Labels | Small, Bold | `text-sm font-bold` |
| Body Text | Regular | `text-base font-medium` |
| Muted Text | Small, Gray | `text-xs text-gray-600` |

---

## Spacing & Layout

| Use Case | Value |
|----------|-------|
| Page Padding | `p-6` (top/bottom), `py-12` or `py-16` |
| Card Padding | `p-6` or `p-8` |
| Gap Between Cards | `gap-6` or `gap-8` |
| Gap Inside Cards | `space-y-5` or `mb-4`/`mb-6` |
| Border Radius (Cards) | `rounded-2xl` |
| Border Radius (Buttons) | `rounded-xl` |
| Shadows | `shadow-lg` + `shadow-indigo-*/*` |

---

## Animation & Effects

### Fade-In Animation
```html
<div class="animate-fade-in-up" style="animation-delay: 0ms;">
```
**Defined in** `public/css/animations.css`

### Backdrop Blur
```html
<div class="backdrop-blur-sm">
```
Frosted glass effect on cards and overlays.

### Hover Effects
- **Lift**: `hover:-translate-y-0.5` or `hover:-translate-y-1`
- **Scale**: `hover:scale-105`
- **Shadow**: `hover:shadow-2xl`
- **Color**: `:hover` state for gradients

---

## Pages Updated (V2 Completion Checklist)

### Public Pages
- [x] **Navbar** (`resources/views/navbar.blade.php`)
  - Gradient card background
  - Centered floating design
  - Admin pill with link
  
- [x] **Home** (`resources/views/home.blade.php`)
  - Hero background gradient
  - "View Projects" button with gradient
  - Accent text styling
  
- [x] **About** (`resources/views/about.blade.php`)
  - Hero background gradient
  - Gradient card container
  - Centered layout
  
- [x] **Projects** (`resources/views/projects.blade.php`)
  - Project cards with soft gradient backgrounds
  - Hover scale & shadow effects
  - "Details" buttons with gradient
  - Tech tag styling
  - Staggered animation on load
  
- [x] **Project Detail** (`resources/views/projects/show.blade.php`)
  - Swiper slider with gradient card background
  - Technology tags
  - Project metadata

### Admin Pages
- [x] **Admin Login** (`resources/views/admin/login.blade.php`)
  - Gradient page background
  - Centered card with white/indigo gradient
  - Password input with focus states
  - Login button with gradient
  - Hint box with muted styling
  
- [x] **Admin Index** (`resources/views/admin/projects/index.blade.php`)
  - Header with accent gradient
  - Logout button with backdrop blur
  - Project cards with image overlay
  - Image count badge
  - Manage button with gradient
  - Tech tag display (first 2 + count)
  
- [x] **Admin Edit** (`resources/views/admin/projects/edit.blade.php`)
  - Back link with hover state
  - Two-column layout (details + images)
  - Form inputs with indigo borders & focus states
  - Upload form with file input styling
  - Image gallery with hover overlays
  - Delete buttons on image hover
  - Success/error messages with alerts

---

## Remaining Features (Optional Enhancements)

### Phase 2 (Not Yet Implemented)
- [ ] Drag-and-drop image reordering
- [ ] Inline caption editing via AJAX
- [ ] Replace session auth with Laravel Breeze
- [ ] Mobile nav drawer (hamburger menu)
- [ ] Dark mode toggle
- [ ] Image lazy-loading optimization
- [ ] Loading skeletons for async content

---

## Quick Reference: Key Tailwind Classes

```
Gradients:
  from-indigo-500 via-cyan-500 to-emerald-500
  from-indigo-50 via-blue-50 to-cyan-50
  to-transparent (fading gradients)

Shadows:
  shadow-lg shadow-indigo-200/50
  shadow-2xl shadow-indigo-300/40

Spacing:
  p-6 / p-8
  space-y-5 / gap-6
  py-12 / py-16

Effects:
  hover:scale-105
  hover:-translate-y-0.5
  backdrop-blur-sm
  rounded-2xl / rounded-xl

Text:
  text-indigo-900 / text-gray-700
  font-extrabold / font-bold
  text-4xl / text-2xl / text-sm
```

---

## Testing Checklist

- [ ] All gradients render on Chrome, Firefox, Safari
- [ ] Cards have proper spacing and shadows
- [ ] Buttons respond to hover (desktop & touch)
- [ ] Form inputs show focus states clearly
- [ ] Mobile layout stacks correctly (responsive grid)
- [ ] Images load and display in correct aspect ratio
- [ ] Admin dashboard loads without errors
- [ ] Login form is accessible & properly labeled
- [ ] Success/error messages are visible

---

## Future Considerations

1. **CSS Variables**: Could export color palette to `public/css/app.css` for easier theming
2. **Component Library**: Consider moving to a dedicated component system (Livewire components, Vue)
3. **Accessibility**: Ensure all interactive elements have proper `aria-*` labels
4. **Performance**: Monitor Tailwind bundle size; consider purging unused classes
5. **Dark Mode**: If required, extend Tailwind with `darkMode: 'class'`

---

**Last Updated:** December 23, 2025  
**Status:** V2 Public & Admin pages complete. Ready for Phase 2 enhancements.
