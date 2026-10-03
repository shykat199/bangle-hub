Modern UI (Mobile-friendly) applied

What I changed (non-breaking):
1) Added a lightweight CSS skin at public/css/modern-ui.css
2) Included the CSS in your main layout(s) by editing the <head> to add:
   <link rel="stylesheet" href="{{ asset('css/modern-ui.css') }}">
3) Ensured mobile meta viewport is present.
4) Added a small JS helper at public/js/modern-ui.js (optional) which toggles a sidebar if you have an element #sidebarToggle and a .sidebar element.
5) Kept all functionality the same. No route/view names changed.

How to use:
- If your sidebar exists, add a button with id="sidebarToggle" in your topbar for mobile.
- Tables are automatically horizontally scrollable on small screens.
- Buttons, cards, forms, and badges have a refreshed look without breaking existing classes.

If anything looks off in a specific page, it's likely due to very custom CSS in that page.
In that case, let me know the file, and I'll fine-tune the styles.
