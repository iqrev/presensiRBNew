# Project Rules

- **Minimalist UI & No Emojis**: Avoid using default HTML emojis (like 👤, 💡, 💾) in the user interface. Always use proper vector icons (e.g., Phosphor Icons `<i class="ph ph-icon-name"></i>`) to maintain a clean, modern, and professional minimalist blue/white theme.
- **Clear Error Messages (Indonesian)**: Always ensure error messages and validation feedback sent to the user are clear, human-readable, and in Indonesian. Avoid returning raw translation keys (like `validation.unique`). Use Laravel's localized `lang/id` or specify custom error messages in the controllers.
