# ShopEase — Instructions for Codex

## Project purpose
Build ShopEase, a staged e-commerce learning project using CodeIgniter 4, PHP, MySQL, and Bootstrap 5 for PBKK coursework. Implement only the scope of the meeting currently requested by the user. The three meeting directories are independent snapshots, not one shared application.

## Repository layout (authoritative)
- `Studi_Kasus_ShopEase_CodeIgniter4.docx` — case study.
- `PRD_ShopEase_CodeIgniter4.docx` — product requirements.
- `HOL_ShopEase_CodeIgniter4.docx` — hands-on material.
- `references/Referensi_ShopEase_Pertemuan_12/Dokumen/` — additional authentication and product-management labs.
- `references/Referensi_ShopEase_Pertemuan_12/Infografis/` — architecture, ERD, routing, UI and implementation visual references.
- `Pertemuan 10/`, `Pertemuan 11/`, `Pertemuan 12/` — separate CI4 project snapshots.

Read the relevant original documents and images before implementing related features. Treat the written assignment and hands-on lab as the source of functional requirements; infographics are supplementary and may contain illustrative code or inconsistent details. If sources disagree, report the discrepancy and ask before making a consequential choice. Do not claim to have read an unsupported format if it cannot be opened.

## Strict scope and snapshot rules
1. **Pertemuan 10 — MVC and routing:** CI4 setup, explicit public routes, controller/view structure, responsive landing page and static sample catalog. No unnecessary database, authentication, or CRUD implementation.
2. **Pertemuan 11 — Models:** build upon a copy of the completed Pertemuan 10 snapshot in `Pertemuan 11/`; connect to MySQL and use CI4 models for dynamic categories/products and catalog views. Do not introduce the complete administrative CRUD workflow unless explicitly requested.
3. **Pertemuan 12 — CRUD/Product Management:** build upon a copy of completed Pertemuan 11 in `Pertemuan 12/`; implement authentication/session/role protections as needed, category CRUD, product CRUD, search/filter, price/stock/status, image management, and testing according to the supplied labs. Support Admin and Operator access as specified in the source materials. Shopping cart, checkout, payments, shipments and order management are outside this assignment unless requested.
4. Never edit an earlier meeting's project while implementing a later one. Never delete or overwrite a completed snapshot. Ask before copying/restructuring an existing project if that operation risks overwriting files.
5. Work in the requested meeting only. If the active meeting is unclear, ask which meeting to implement.

## Database
- Pertemuan 10: no database required.
- Pertemuan 11: `db_shopease_p11`.
- Pertemuan 12: `db_shopease_p12`.
- The lab may call the database `db_shopease`; use the meeting-specific names above in local environment settings, while preserving the lab's table and field specifications where applicable.
- MySQL is accessed through the user's local XAMPP setup. Inspect the actual port and credentials; port `3307` is a prior local convention, **not** an assumption to hardcode without checking.
- Prefer repeatable CI4 migrations and seeders. Respect foreign keys, uniqueness, decimal money values and category/product/image relationships. Never drop or reset existing databases without explicit approval.
- Never commit `.env`, passwords, private keys, database exports containing real personal information, or other secrets.

## Visual direction — mandatory
- Brand direction: **white + navy blue + restrained blue + orange**, not purple.
- Suggested tokens: white `#FFFFFF`, slate background `#F8FAFC`, navy `#142D4E`, blue `#2563EB`, orange `#F97316`.
- Use navy for headings/navigation, blue for links and selected states, and orange selectively for primary commerce actions (e.g. Add to Cart / Checkout), not every button.
- Bootstrap 5; clean spacing, consistent typography, subtle borders, compact functional tables, accessible contrast, mobile responsiveness.
- Avoid gradients, neon glows, glassmorphism, excessive shadows, oversized rounded cards, decorative charts, random emojis and generic AI-generated dashboard aesthetics.
- The supplied purple infographics are **functional/layout references**, not the required color palette. Do not copy their purple branding.
- Keep one coherent visual system across all three meeting snapshots.

## Code and security
- Follow the actual installed CodeIgniter 4 version and its APIs. Use MVC separation: Routes -> Controllers -> Models -> MySQL; controllers render views.
- Prefer explicit HTTP-method routes and route groups. Disable unsafe auto-routing for protected functionality where appropriate.
- Validate and normalize server-side input; escape user content in views; use CSRF protection for state-changing forms; use parameterized query/model APIs.
- Passwords must be hashed with `password_hash()` and verified with `password_verify()`; regenerate session on login; check login and role server-side, not just by hiding menu links.
- Apply least-privilege role checks for Admin/Operator actions. Confirm exact permissions from the assignment before making assumptions.
- Validate uploaded image MIME/type, size and extension; use generated safe filenames; prevent path traversal and executable uploads; ensure uploads cannot overwrite arbitrary files. Manage removal of orphan files safely.
- Do not silently change the supplied schema or invent additional business requirements. If a documented example is incomplete or insecure, implement a safe compatible solution and explain the deviation.

## Working procedure
1. Before editing: inspect the requested meeting folder, relevant documents, existing routes/models/views, `.env` presence (never print secrets), and database configuration.
2. Present a brief plan and list the files likely to change. Keep changes focused on the active stage.
3. Implement in small, testable increments; avoid unnecessary dependencies and refactors.
4. Test with appropriate commands, e.g. `php spark routes`, PHP syntax checks, and targeted CI4 tests; verify responsive pages and role-protected paths when possible. Do not claim tests passed unless actually executed.
5. At the end of each stage, report changed files, implemented features, exact commands/tests run, results, outstanding issues and manual verification steps.
6. Do not automatically create commits, push, open PRs, or generate lengthy documentation unless the user requests it. Do not touch other coursework repositories.
7. Never run destructive database commands or migrations against an existing populated database without asking for approval.

## Stage plan (internal checkpoints, not extra coursework meetings)
- P10-1: CI4 project setup and routing.
- P10-2: static landing page and responsive MVC views.
- P11-1: schema, migrations, seed data and models.
- P11-2: dynamic catalog/category/product detail.
- P12-1: authentication and role-based filter.
- P12-2: protected admin/operator layout.
- P12-3: category CRUD.
- P12-4: product CRUD, search/filter, stock and status.
- P12-5: product image uploads and management.
- P12-6: integration testing, fixes, and documentation/infographic **when requested**.

## Agent communication
Respond in Indonesian. Be concise, specific, and transparent about limitations. Prioritize implementing the current requested stage over Git operations or polishing documentation. Stop and ask if a critical ambiguity would change data integrity, permissions, or the preservation of earlier meeting snapshots.
