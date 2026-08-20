# Medora
# Medora HMS

<p align="center">
  <img src="public/images/medora-logo.png" alt="Medora logo" width="104">
</p>

<p align="center">
  <strong>A modern, role-based hospital management platform for connected care.</strong>
</p>

<p align="center">
  <a href="#features">Features</a> ·
  <a href="#technology">Technology</a> ·
  <a href="#getting-started">Getting started</a> ·
  <a href="#user-roles">User roles</a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white" alt="PHP 8.3 or newer">
  <img src="https://img.shields.io/badge/MySQL-Database-4479A1?logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?logo=tailwindcss&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/JWT-Secured-0F172A" alt="JWT secured">
</p>

![Medora healthcare experience](public/images/home-hero.png)

## About Medora

**Medora HMS** is a full-stack Hospital Management System that brings essential clinical, administrative, and operational workflows into one secure platform. It is designed around the people who use it every day—administrators, doctors, hospital staff, and patients.

The system combines a polished healthcare-focused interface with role-based access, live operational data, and structured hospital records. From a patient’s first registration to appointments, medical records, laboratory requests, prescriptions, billing, and analytics, Medora helps a care team work from a single source of truth.

> Built as a portfolio and learning project to demonstrate practical full-stack development, healthcare workflows, secure authentication, and responsive product design.

## Features

### Core care workflows

- **Patient management** — register, search, view, update, and archive patient profiles.
- **Department and doctor management** — organize departments, doctor profiles, specializations, schedules, and availability.
- **Appointment management** — book appointments and manage their clinical status from scheduling through completion.
- **Electronic Medical Records (EMR)** — record consultations, diagnoses, treatment plans, prescriptions, and follow-up details.
- **Laboratory management** — create test requests, track samples and status, record results, and prepare printable reports.
- **Pharmacy management** — manage medicine catalogues, batches, stock receipts, expiry alerts, low-stock alerts, and dispensing.
- **Billing and payments** — create invoices, capture payments, track balances, and view outstanding amounts.
- **Staff management** — maintain employee profiles, daily attendance, and leave requests.
- **Reports and analytics** — review live patient, appointment, financial, pharmacy, laboratory, and staff activity; export data to CSV and print reports.

### Secure user experiences

- JWT-based authentication for API requests.
- Role-based navigation, dashboards, and API authorization.
- Dedicated **Admin**, **Doctor**, and **Patient** dashboards.
- Doctor workspace for reviewing patients, appointments, and medical records.
- Patient portal for viewing their own profile and updating contact/sign-in details.
- Protected access boundaries: patients can only access their own portal data, while clinical record changes are limited to authorized hospital roles.

### Product design

- Responsive desktop and mobile navigation.
- Consistent clinical workspace design system for forms, data tables, filters, cards, and alerts.
- Branded public landing page, sign-in experience, authenticated dashboards, and patient portal.
- Accessible focus states and reduced-motion support.

## User roles

| Role | Main capabilities |
| --- | --- |
| **Administrator** | Full operational access: patients, doctors, departments, appointments, laboratory, pharmacy, billing, staff, and reports. |
| **Doctor** | Clinical workspace, patient directory, appointment visibility, personal profile management, and medical record creation/editing. |
| **Nurse / Receptionist** | Access to the relevant patient, appointment, department, and doctor workflows. |
| **Laboratory Staff** | Laboratory requests, sample collection, test processing, and result workflows. |
| **Pharmacist** | Medicine catalogue, stock, batch expiry monitoring, and prescription dispensing. |
| **Accountant** | Billing, invoices, payments, and balances. |
| **Patient** | A private patient portal for their own profile and account credentials only. |

## Technology

| Area | Technologies |
| --- | --- |
| Backend | Laravel 13, PHP 8.3+, Eloquent ORM, Laravel Mail |
| API security | JWT authentication, role-based middleware, request validation |
| Database | MySQL, migrations, seeders, relational data modelling |
| Frontend | Blade templates, JavaScript modules, Tailwind CSS 4 |
| Tooling | Vite, npm, Composer, PHPUnit |

## Project structure

```text
app/
├── Http/            # Controllers, middleware, and request validation
├── Mail/            # Doctor and patient account mailables
├── Models/          # Domain models and relationships
└── Services/        # Business workflows
