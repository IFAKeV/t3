# AGENTS.md

## Code Style
- Format all Python code with **Black**.
- Avoid abbreviations in variable names.
- Comment inline when modifying existing code so the purpose is clear.

## Testing
- Run `python -m py_compile app.py database.py config.py addressbook.py`.
- Execute `pytest` and `flake8` if available.

## Workflow
- Perform changes in branch `codex` only and avoid commits to `main`.
- Work on the listed tasks sequentially and keep them well separated.
- Reuse existing fields, templates and modules when possible.
- Place new helper functions in dedicated modules such as `mailer.py`.

## PR Instructions
- Use the title format `[Fix] Short description`.
- Provide a brief summary and a **Testing Done** section.
