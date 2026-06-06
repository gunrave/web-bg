---
version: 1
mode: solo
models:
  research: opencode-go/deepseek-v4-flash
  planning: opencode-go/deepseek-v4-pro
  discuss: opencode-go/deepseek-v4-pro
  execution: opencode-go/qwen3.7-max
  execution_simple: opencode-go/qwen3.7-max
  completion: opencode-go/qwen3.7-max
  validation: opencode-go/deepseek-v4-pro
  subagent: opencode-go/qwen3.7-max
git:
  isolation: worktree
  main_branch: main
  auto_push: true
token_profile: budget
verification_commands:
  - npm run build
---
# GSD Skill Preferences

See `~/.gsd/agent/extensions/gsd/docs/preferences-reference.md` for full field documentation and examples.
