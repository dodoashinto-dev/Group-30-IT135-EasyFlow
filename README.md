# EasyFlow
EasyFlow is a menstrual health app that helps users track their cycles and estimate period dates, ovulation, and fertile windows. Users can opt in to receive SMS reminders as important cycle dates approach.

> Women can track their menstrual cycle, predict key reproductive phases, and receive timely reminders so they can plan daily activities with confidence and better health awareness.

## The problem
Many women still track their periods manually with notes, calendars, or memory, which is often inconsistent and difficult to interpret. This makes it hard to predict ovulation, fertile days, and period-related changes before they happen. Without timely reminders, users may miss important cycle events that affect their health, daily planning, and wellbeing.

## Core features
1. Cycle prediction engine - estimates the next period date, cycle length, and stage progression.
2. Ovulation and fertile window tracking - identifies the most likely ovulation and fertile days.
3. SMS reminder notifications - sends alerts before upcoming periods and key cycle milestones.

Later (out of scope for now): symptom tracking, calendar export, fertility notes, and advanced health insights.

## External service and data
- External service: Arkesel SMS API for reminder notifications to users
- Data stored: user profiles, menstrual cycle history, prediction results, ovulation dates, fertile window data, and reminder preferences

## Phone accounts and SMS reminders
The app uses an international phone number (E.164 format, for example `+233241234567`) and password for registration and login. At registration, users can explicitly opt in to period reminders; only opted-in users receive them. A reminder is sent three days before the predicted next period.

1. Back up the database, then run `EasyFlow/migrations/20261115_phone_sms.sql` against the existing `cycle_tracker` database. It preserves the old username as `legacy_username`; for existing accounts, fill in each account's `phone_number` with the user's real international number before relying on phone login. The new reminder preference defaults to off.
2. Set `ARKESSEL_API_KEY` and `ARKESSEL_SENDER_ID` in the environment used by PHP. Keep the API key private; do not commit it to the repository.
3. Ensure PHP's cURL extension is enabled and authorize the sender ID in your Arkesel account.
4. Schedule `php EasyFlow/send_reminders.php` to run daily using Windows Task Scheduler or cron. The script records successful sends to prevent repeats; failed requests are reported and eligible for retry on a later run.

The SMS sender uses Arkesel's [SMS API](https://developers.arkesel.com/) (`POST /api/v2/sms/send`).

## Team (roles rotate every 3 weeks)
| Name | GitHub | Role (weeks 1-3) |
|------|--------|------------------|
| To be assigned | @username | Project manager  |
| To be assigned | @username | Lead developer   |
| To be assigned | @username | Tester           |
| To be assigned | @username | Documentation lead |

## Process model
Agile Scrum - see docs/02-process-model/process-model.md

## Documentation
| Folder | Contents |
|--------|----------|
| docs/01-proposal | Problem statement, member profiles |
| docs/02-process-model | Process model justification |
| docs/03-requirements | SRS, requirements, elicitation notes |
| docs/04-uml | UML diagrams |

## How to run
1. Clone the repository to your local machine.
2. Install the required frontend and backend dependencies.
3. Configure the environment variables for the database and SMS service.
4. Start the application locally.
5. Open the app in a browser, register a user, and enter cycle information to see predictions and reminders.