# test-manager-library-3145556-api

> **Prueba técnica · Ficha ADSO 3145556**
> Entrega: **martes 13 de octubre de 2026, a las 5:00 p. m.** (17:00, hora Colombia).

Este repositorio es el **backend** de *Manager Library*, en **PHP con Laravel**. **Empieza vacío a propósito**: se construye en el fork de cada aprendiz.

## Instrucciones

Cada aprendiz debe **crear el fork** de los seis repositorios del proyecto y **resolver el proyecto
con el spec planteado**.

1. Hacer fork, a su cuenta de GitHub, de cada repositorio de la tabla del final.
2. Leer el spec en [`test-manager-library-3145556-docs`](https://github.com/code-sena/test-manager-library-3145556-docs).
   Se entrega en dos versiones: `spec-python/` y `spec-.net/`.
3. Desarrollar en los forks.

## El reto se desarrolla con React y PHP (Laravel)

El spec está escrito para Python y para .NET, pero el reto **no** se hace en esos lenguajes:

| Capa | Tecnología del reto |
|---|---|
| Frontend | React |
| Backend | PHP con Laravel |

Lo que el spec define sobre el negocio —historias, criterios de aceptación, reglas, contrato de la
API, modelo de datos— se respeta. Lo que define sobre la tecnología se traduce a React y Laravel.

## La prueba no consiste en escribir el código

El propósito principal es ver la **capacidad de desempeño con SDD** (*Spec-Driven Development*,
desarrollo guiado por especificación): cómo se lee, se interpreta y se aplica una especificación
para llevarla a un stack distinto. El código es el medio, no el fin.

## Entrega

Cuenta lo que quede **subido a los forks antes de las 5:00 p. m.** (17:00). Lo que llegue después no se revisa.
No hay prórroga: suban avances parciales con commits pequeños durante la prueba.

## Los seis repositorios

| Repositorio | Qué va ahí |
|---|---|
| [`test-manager-library-3145556-docs`](https://github.com/code-sena/test-manager-library-3145556-docs) | El spec: `spec-python/` y `spec-.net/` |
| [`test-manager-library-3145556-api`](https://github.com/code-sena/test-manager-library-3145556-api) | Backend en PHP (Laravel) |
| [`test-manager-library-3145556-app`](https://github.com/code-sena/test-manager-library-3145556-app) | Frontend en React |
| [`test-manager-library-3145556-page`](https://github.com/code-sena/test-manager-library-3145556-page) | Sitio público estático de presentación |
| [`test-manager-library-3145556-infra`](https://github.com/code-sena/test-manager-library-3145556-infra) | Contenedores, red, volúmenes y motor de base de datos vacío |
| [`test-manager-library-3145556-tool`](https://github.com/code-sena/test-manager-library-3145556-tool) | Utilidades: sembrador de datos de demostración |

## Author

José Sebastián Suárez Cumaco, GitHub: [JoseSebastianSuarezCumaco](https://github.com/JoseSebastianSuarezCumaco).

This repository is my fork of `code-sena/test-manager-library-3145556-api`, developed for the SDD technical test
of ficha ADSO 3145556 (Laravel backend).

## Implementation status (SDD traceability)

Each task is done only when its evidence exists and can be run (art. XII). Task numbers come from `tasks.md`.

| Task | Status | Commit | Evidence |
|---|---|---|---|
| T-01 Skeleton and `GET /health` | Done | `ccd3ad0` | `GET /api/v1/health` returns `{"status":"ok"}` |
| T-02 Initial migration | Done | `91749fb` | 5 seeded categories; 0 columns with a `DEFAULT` |
| T-03 Value objects (`Isbn`, `Quantity`, `DateRange`) | Done | `e71e3bd` | `php artisan test --filter=ValueObjectsTest`: 7 pass |
| T-04 `Book` | Done | `fc6c757` | `php artisan test --filter=BookTest`: 9 pass |
| T-05 `Loan` | Done | `a8e620e` | `php artisan test --filter=LoanTest`: 12 pass |
| T-06 `Member` and `User` | Done | `4b024df` | `php artisan test --filter=MemberAndUserTest`: 7 pass |
| T-07 to T-19 | Pending | | Use cases, persistence, security, covers, HTTP routes, composition, architecture tests |

The domain layer (T-03 to T-06) is complete and has no dependency on Laravel, the database or HTTP.

## Design decisions

- **Concurrency without a new column.** ADR-002 mentions a "version witness" in `book`, but `data-model.md` has no
  such column and that document wins (art. X). The decision is optimistic concurrency with an atomic
  `UPDATE ... SET available_copies = available_copies - 1 WHERE id = ? AND available_copies >= 1` inside the
  transaction. If it affects 0 rows, the answer is 409 `BOOK_NOT_AVAILABLE`. The `ck_book_available_range`
  CHECK is the last barrier.
- **Data model untouched.** No extra columns and no `DEFAULT`. All DDL lives in the application migration (ADR-001);
  `infra` has no `CREATE TABLE`.
- **T-18 constraints.** `ck_book_total_positive` and `ck_user_role` are marked "pending (T-18)" in `data-model.md`,
  so they are not in the initial migration.
- **Admin user.** It is not seeded and cannot be created through the API (requesting the admin role is a 422). It
  is created at startup from `ADMIN_USERNAME` and `ADMIN_PASSWORD` (T-17). The domain exposes `User::provisionAdmin`
  for that.
- **Hexagonal architecture in Laravel.** `app/Domain` has no dependencies; `app/Application` will hold use cases and
  their ports; `app/Adapters` will hold persistence, security, covers and HTTP; a single service provider will do
  the composition. An architecture test will replace `import-linter` (T-19).
- **Calculated loan status.** `open`, `overdue` and `closed` are calculated with an injected `Clock` and never
  stored. `overdue` is strict (`due_at < now`), following CA-09.1.
- **Rules kept out of `Loan`.** Max 3 unreturned books per member and members with overdue loans need member data,
  so they belong to the lend use case (T-09). A book with open loans cannot be soft-deleted: this is checked in
  the books use case (T-08).
- **Laravel defaults.** `SESSION_DRIVER`, `CACHE_STORE` and `QUEUE_CONNECTION` do not use the database, so Laravel
  creates no tables of its own. The default `App\Models\User` and the `users` table do not belong to the spec
  (`library.user`) and will be removed when authentication is built.
- **Local port.** `.env.example` keeps the standard `DB_PORT=5432`; the local `.env` uses 5433 because another
  PostgreSQL occupies 5432 on the development machine. The real `.env` is never committed.
- **Language.** Code, commits, branches and documentation are in English. The `message` field of API errors is
  currently written in Spanish, as the spec asks (RN-6, art. XI); this is pending confirmation.
- **No invented conventions.** The spec imposes no branch or commit convention. Work is done on `main` with one small
  commit per task and progress pushed after each one.