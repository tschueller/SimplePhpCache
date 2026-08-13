# Copilot Workspace Instructions

Follow AGENTS.md in repository root as the primary, tool-neutral instruction source.
This file adds Copilot-specific guidance only and must not contradict AGENTS.md.

## Copilot-Specific Behavior
- Prefer small, focused edits over broad rewrites.
- Keep output concise and practical.
- Ask when requirements are unclear.
- Ask before implementing any potential breaking change.
- Preserve backward compatibility by default.
- Avoid overbuilding for this small project.

## Required Checks in Change Summaries
For relevant changes, always report:
- changelog status
- test impact status
- validation commands executed and results
- security review notes
- TODO.md additions for deferred work

## Scope Guardrails
- Do the necessary modernization baseline first.
- Keep tests to core behavior unless explicitly expanded.
- Move optional or larger follow-up work to TODO.md.
- If a change affects documented behavior, update README.md.
- If a breaking change is approved and implemented, ensure README.md contains migration guidance.