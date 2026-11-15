# Group-30-IT135-cycle_tracker
cycle_tracker is a web app designed to help women predict menstrual cycle phases, free days, ovulation periods, and fertile windows. The app can also send SMS reminders when users are approaching important cycle events so they can plan ahead more effectively.

# cycle_tracker

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