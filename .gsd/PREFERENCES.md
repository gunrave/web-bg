---
version: 1
mode: solo
models:
  research: opencode-go/deepseek-v4-flash
  planning: opencode-go/deepseek-v4-pro
  discuss: opencode-go/deepseek-v4-pro
  execution: opencode-go/deepseek-v4-flash
  execution_simple: opencode-go/deepseek-v4-flash
  completion: opencode-go/deepseek-v4-flash
  validation: opencode-go/deepseek-v4-pro
  subagent: opencode-go/deepseek-v4-flash
git:
  auto_push: true
  main_branch: main
  isolation: branch
token_profile: budget
verification_commands:
  - npm run build
---
# GSD Skill Preferences

See `~/.gsd/agent/extensions/gsd/docs/preferences-reference.md` for full field documentation and examples.
