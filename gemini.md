# Project PresensiRBNew - AI Development Guidelines

This file serves as a reference for Gemini / AI Agents to provide more efficient, consistent, and context-aware assistance when developing this project.

## 1. Tech Stack
- **Backend & Framework**: Laravel 11 (PHP 8.2+). Uses standard MVC, Eloquent ORM, and Blade templating.
- **Frontend & Styling**: TailwindCSS v4 with Vite (`@tailwindcss/vite`). Alpine.js (`x-data`, `x-if`, dll.) for lightweight reactivity in Blade views without compiling bulky JS frameworks.
- **Face Recognition**: `face-api.js` loaded locally for fast client-side 1:N face matching and liveness checks without external API costs.
- **Icons**: Phosphor Icons (e.g. `<i class="ph ph-user"></i>`).

## 2. Design & UI/UX Principles
- **Minimalist & Professional UI**: Maintain a clean, fast, and modern look (white/blue/slate standard theme). 
- **Icons Over Emojis**: Strictly avoid using default text emojis (❌👤, 💾). Always use proper Phosphor vector icons (✔️ `<i class="ph ph-user"></i>`).
- **Responsive First**: Ensure UI looks great on mobile, as attendance apps are often accessed via smartphones.
- **Predictable Feedback**: Every action must have definitive loading states (spinners) and clear success/error prompts.

## 3. Code Standards & Commit Rules
- **Controller Logic**: Keep controllers thin. Delegate complex business logic (like distance calculation, face distance computation) to specialized Services (e.g., `AttendanceService`, `GeofencingService`).
- **Error Messages**: Always ensure error messages and validation feedback are provided in clear, human-readable **Indonesian language**. Do NOT expose raw translation keys (e.g., `validation.unique`). Treat employees/users politely in error messages (e.g., "Maaf, wajah tidak terdeteksi").
- **Asset/Client Code**: No heavy node modulus on the frontend; Alpine & simple JS functions should be kept within Blade `@push('scripts')` to maximize load speed and keep things organized.

## 4. Specific Business Logic Constraints
- **Geofencing**: Relies on Haversine formula calculation. The system supports multiple active `OfficeLocation`.
- **Attendance Flow**: Before taking a photo, GPS must be established and Liveness steps completed successfully.
- **Camera Handling**: Keep user-facing preview mirrored (`transform: scaleX(-1)`) to act as a mirror, but capture the real canvas context without flipping so texts/logos on uniforms are readable upon save.
