# 🧠 Multi-Agent Development Workflow (HITL·Work)

A collection of 16 system prompts for a multi-agent coding workflow: **Decomposer → Planner → Executor → QA**, complemented by specialist agents for review, debugging, refactoring, and documentation.

Each `.md` file is an independent system prompt for a single role. The core principle: **one agent, one responsibility.** The Planner does not write code. The Executor does not think about architecture. QA does not trust anything without verification. This separation allows each agent to be driven by even cheap/fast models because their scope is narrow and clear.

---

## Main Pipeline

```
(Decomposer) → Planner → (Doubt Review) → Executor → QA → (Review specialists) → Documentation
```

0. **Decomposer** (optional, only for large scopes) — if the input is a large PRD spanning multiple modules, break it down first into smaller tasks (`task-1.md`, `task-2.md`, ...) before handing it over to the Planner.
1. **Planner** creates a detailed and unambiguous spec (`prd.md`) — per task (if routed through Decomposer) or directly from a feature request.
2. **Doubt Review** (optional but recommended) stress-tests the spec before execution — looking for hidden assumptions, missed edge cases, and scope creep.
3. **Executor** implements exactly according to the spec, no improvisations.
4. **QA** verifies that the implementation matches the spec — skeptical by default.
5. Review specialists (**Architecture, Security, Performance, Migration, UI/UX**) run in parallel or after QA, depending on what the feature touches.
6. **Documentation** & **Skill Extractor** close the loop — documenting what was built and what patterns should be remembered for the next feature.

---

## Agent List

### Intake

| Agent | File | Role |
|---|---|---|
| 🧩 **Decomposer** | `decomposer.md` | The first agent in the pipeline if the input is a large PRD. Reads the PRD and breaks it down into discrete tasks based on domain/functional boundaries (not implementation steps), each saved as `task-N.md` with Goal + Context (inter-task dependencies) fields. Does not plan, does not execute — purely decomposes. Always stops for human review before passing tasks to the Planner. |

### Core Pipeline

| Agent | File | Role |
|---|---|---|
| 🧠 **Planner** | `planner.md` | Senior Software Architect. Translates feature requirements (or a single `task-N.md` from the Decomposer) into a complete implementation spec (`prd.md`) — including necessity checks (YAGNI+DRY), reusability assessments, security/performance/observability/UI-UX checks. Does not write code. |
| ⚙️ **Executor** | `executor.md` | Meticulous Implementation Engineer. Executes `prd.md` step by step, without improvisation. Stops and asks if any step is ambiguous or blocked. |
| 🔍 **QA** | `qa.md` | Quality Assurance Engineer. Verifies the Executor's output against the spec — spec compliance, breaking changes, Definition of Done, and basic code quality (logic, security, side effects, consistency). |

### Pre-Execution Review

| Agent | File | Role |
|---|---|---|
| 🔍 **Doubt Review** | `doubt-review.md` | Adversarial spec reviewer. Run after the Planner, before the Executor. Hunts for hidden assumptions, scope creep, irreversible steps, missing edge cases, and ambiguities — before any code is written. |

### Post-Execution Review Specialists

Run after the Executor finishes, usually in parallel with or after QA. All agents in this category **only report issues, they do not fix them**.

| Agent | File | Focus |
|---|---|---|
| 🏛️ **Architecture Review** | `architecture-review.md` | Compliance with skill files, layer boundaries (Controller/Service/Model), dependency direction, duplication vs reuse, long-term maintainability. |
| 🔒 **Security Review** | `security-review.md` | Attack surface, input validation, auth/authz, data exposure, hardcoded secrets, dependency risk. |
| ⚡ **Performance Review** | `performance-review.md` | N+1 queries, indexing, caching, memory usage, external call resilience, scalability at 10x–100x load. |
| 🗄️ **Migration Review** | `migration-review.md` | Database migration safety — risk classification of operations, rollback safety, data integrity, locking risks, legacy code compatibility. |
| ♻️ **Refactor Agent** | `refactor-agent.md` | Finds opportunities for abstraction, readability issues, over-engineering, and coupling — without changing behavior. Outputs a prioritized refactor plan, not code. |

### UI/UX Specialists

| Agent | File | Role |
|---|---|---|
| 🎨 **UI/UX Planner** | `ui-ux-planner.md` | Invoked by the main Planner when UI complexity is high. Produces precise UI specs — color/spacing tokens, components to reuse, states (loading/empty/error), so the Executor doesn't have to guess the visuals. |
| 🧩 **UI Skill Extractor** | `ui-skill-extractor.md` | Reads the UI implementation that has passed QA and extracts/updates the project's visual skill file (`skills/[project]-ui.md`) — tokens, components, anti-patterns — so the next UI audit doesn't start from scratch. |

### Supporting Agents

| Agent | File | Role |
|---|---|---|
| 🔌 **API Agent** | `api-agent.md` | Senior API Architect with 3 modes: **DESIGN** (creates new API contracts), **INTEGRATION** (specs external API consumption — resilience, retry, circuit breakers), **AUDIT** (reviews existing APIs/integrations). |
| 🐛 **Debug Agent** | `debug.md` | Debug investigator. Hunts for the root cause of bugs via config-vs-bug triage, execution tracing, and evidence-based hypotheses. Does not write fixes — provides precise handoffs to the Executor. |
| 📚 **Skill Extractor** | `skill-extractor.md` | Observes existing code and distills patterns into `skill.md` — folder structures, naming conventions, anti-patterns — to be read by all other agents before they start working on the codebase. |
| 📖 **Documentation** | `documentation.md` | Technical writer. Writes documentation (API reference, module docs, feature guides, changelogs) based on the code that was *actually* implemented, not just what was planned. |

---

## Why Separate Agents?

- **Narrow scope → more reliable results.** Models (especially cheaper ones) are more accurate when instructed to "just check security" rather than "check everything at once."
- **Cheap models for Executors, stronger models for Planners/Reviewers.** Since the Executor is merely following a detailed spec, it doesn't need heavy reasoning — as long as the spec is comprehensive.
- **Each agent can be called independently.** Only need a security audit? Invoke the Security Review without going through the whole pipeline.
- **Skill files as shared memory.** `skill-extractor.md` and `ui-skill-extractor.md` prevent every Planner/Executor from having to re-audit the codebase from scratch every time.

---

## How to Use

1. **Large PRD with many modules?** Start with the **Decomposer** — break it down into `task-N.md` per domain/module, review and agree on the task list before proceeding.
2. For each task (or directly if the scope is small) — start with the **Planner**, explain the feature/bug you want to work on.
3. If the spec touches high-risk areas (multi-tenant, DB migrations, live users) → run **Doubt Review** before handing off to the Executor.
4. **Executor** implements according to `prd.md`.
5. **QA** verifies.
6. Run the relevant review specialists (e.g., if there's a DB migration → Migration Review; new endpoints → Security + API Agent AUDIT).
7. If everything passes → **Documentation** & **Skill Extractor** (or **UI Skill Extractor** for features with UI) to close the loop.

Every agent will refuse to do work outside their scope (e.g., Planner refuses to write code, QA refuses to approve without reading the actual code) — this is intentional, so each stage remains an independent check on the previous stage.
