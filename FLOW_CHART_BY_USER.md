# Tabulation System Flow Charts by User Role

This document contains detailed flow charts for the Tabulation System, separated by user role. Each section is designed to fit on a standard letter-sized paper (8.5" x 11").

---

## Part 1: System Hierarchy & Overview

### System Hierarchy

```mermaid
graph TD
    Admin[System Admin] -->|Manages| Users[User Management]
    Admin -->|Approves| Pageant[Pageant]
    
    Users -->|Creates| Organizer[Organizer]
    
    Organizer -->|Creates & Manages| Pageant
    Organizer -->|Defines| Criteria[Criteria & Rounds]
    Organizer -->|Registers| Contestants[Contestants]
    Organizer -->|Assigns| Tabulator[Tabulator]
    
    Tabulator -->|Manages| Scoring[Scoring Process]
    Tabulator -->|Assigns| Judge[Judge]
    Tabulator -->|Generates| Results[Results & Reports]
    
    Judge -->|Submits| Scores[Scores]
    
    subgraph "Data Flow"
    Scores -->|Processed by| Tabulator
    Results -->|Verified by| Organizer
    end
```

### User Roles Summary

| Role | Primary Responsibility | Key Actions |
|------|------------------------|-------------|
| **Admin** | System Oversight | Manage users, Approve pageants, View audit logs |
| **Organizer** | Pageant Management | Create pageants, Setup criteria/rounds, Manage contestants |
| **Tabulator** | Scoring Operations | Manage judges, Monitor scoring, Print results |
| **Judge** | Evaluation | Score contestants, View contestant details |

---

<div style="page-break-after: always;"></div>

## Part 2: Admin Workflow

### Admin Process Flow

The Admin is responsible for the integrity of the system and the approval of events.

```mermaid
flowchart TD
    Start((Start)) --> Login[Login to Admin Dashboard]
    Login --> Dashboard{Dashboard Actions}
    
    Dashboard -->|User Mgmt| ManageUsers[Manage Users]
    ManageUsers --> CreateOrg[Create/Verify Organizer]
    ManageUsers --> ManageAdmins[Manage Admins]
    
    Dashboard -->|Pageant Mgmt| ManagePageants[Manage Pageants]
    ManagePageants --> ReviewPending[Review Pending Pageants]
    ReviewPending -->|Decision| ApproveReject{Approve?}
    ApproveReject -->|Yes| Activate[Activate Pageant]
    ApproveReject -->|No| Reject[Reject Pageant]
    
    Dashboard -->|Oversight| ViewLogs[View Audit Logs & Reports]
    
    Activate --> End((End))
    Reject --> End
    ViewLogs --> End
```

### Key Admin Tasks
1.  **User Management**: Create and verify Organizer accounts.
2.  **Pageant Approval**: Review and approve pageants created by Organizers before they can go live.
3.  **System Monitoring**: View audit logs and system reports to ensure fair play.

---

<div style="page-break-after: always;"></div>

## Part 3: Organizer Workflow

### Organizer Process Flow

The Organizer sets up the entire configuration for a specific pageant.

```mermaid
flowchart TD
    Start((Start)) --> Login[Login]
    Login --> CreatePageant[Create New Pageant]
    CreatePageant --> Setup{Pageant Setup}
    
    Setup -->|1. Config| Criteria[Define Criteria & Rounds]
    Setup -->|2. People| Contestants[Register Contestants]
    Setup -->|3. Staff| AssignTab[Assign Tabulator]
    
    AssignTab --> RequestApproval[Request Admin Approval]
    RequestApproval --> Wait{Approved?}
    
    Wait -->|No| Refine[Refine Details]
    Wait -->|Yes| Live[Pageant Live]
    
    Live --> Monitor[Monitor Progress]
    Monitor --> Lock[Lock/Unlock Pageant]
    
    Lock --> End((End))
```

### Key Organizer Tasks
1.  **Pageant Creation**: Define the event details (Title, Date, Venue).
2.  **Criteria & Rounds**: Set up the scoring criteria (e.g., "Beauty 50%", "Intelligence 50%") and rounds (e.g., "Swimwear", "Q&A").
3.  **Contestant Management**: Add contestant profiles and photos.
4.  **Staffing**: Create and assign Tabulator accounts to the pageant.

---

<div style="page-break-after: always;"></div>

## Part 4: Tabulator Workflow

### Tabulator Process Flow

The Tabulator manages the live scoring event and ensures judges are ready.

```mermaid
flowchart TD
    Start((Start)) --> Login[Login]
    Login --> Dashboard[Tabulator Dashboard]
    
    Dashboard -->|Setup| ManageJudges[Create & Assign Judges]
    
    Dashboard -->|Event Control| RoundControl[Manage Rounds]
    RoundControl --> SetRound[Set Current Round]
    SetRound --> Unlock[Unlock Round for Scoring]
    
    Unlock --> Monitor{Scoring Active}
    Monitor -->|Real-time| ViewScores[View Incoming Scores]
    
    ViewScores --> Check{All In?}
    Check -->|No| Remind[Remind Judges]
    Check -->|Yes| Lock[Lock Round]
    
    Lock --> Generate[Generate Results]
    Generate --> Print[Print Reports]
    
    Print --> End((End))
```

### Key Tabulator Tasks
1.  **Judge Management**: Create Judge accounts and assign them to the pageant.
2.  **Round Control**: Control which round is currently active and open for scoring.
3.  **Result Generation**: Monitor scores in real-time, lock rounds when finished, and print official results.

---

<div style="page-break-after: always;"></div>

## Part 5: Judge Workflow

### Judge Process Flow

The Judge focuses solely on evaluating the contestants.

```mermaid
flowchart TD
    Start((Start)) --> Login[Login]
    Login --> Dashboard[Judge Dashboard]
    
    Dashboard --> SelectRound[Select Active Round]
    SelectRound --> ViewContestants[View Contestants]
    
    ViewContestants --> Evaluate[Evaluate Contestant]
    Evaluate --> InputScore[Input Score]
    
    InputScore --> Submit{Submit?}
    Submit -->|Confirm| Saved[Score Saved]
    
    Saved --> Next[Next Contestant]
    Next --> Check{All Done?}
    
    Check -->|No| Evaluate
    Check -->|Yes| Summary[View Summary]
    
    Summary --> End((End))
```

### Key Judge Tasks
1.  **Scoring**: Enter scores for each contestant based on the active round's criteria.
2.  **Review**: View a summary of their submitted scores.
3.  **Comparison**: (Optional) Compare contestants to ensure fair scoring.

---

<div style="page-break-after: always;"></div>

## Part 6: Scoring Calculation Logic

### Score Processing Flow

This diagram illustrates how a single judge's score is processed into a final pageant score.

```mermaid
flowchart TD
    JudgeInput[Judge Submits Score] --> Validate{Validation}
    Validate -->|Invalid| Error[Return Error]
    Validate -->|Valid| Store[Store Raw Score]
    
    Store --> Level1[Level 1: Criteria Weighting]
    Level1 -->|Formula| Calc1["Σ(Score × Weight) / Σ(Weights)"]
    Calc1 --> JudgeScore[Judge Contestant Score]
    
    JudgeScore --> Level2[Level 2: Judge Averaging]
    Level2 -->|Formula| Calc2["Σ(Judge Scores) / Count(Judges)"]
    Calc2 --> RoundScore[Contestant Round Score]
    
    RoundScore --> Level3[Level 3: Round Weighting]
    Level3 -->|Formula| Calc3["Σ(Round Score × Weight) / Σ(Weights)"]
    Calc3 --> FinalScore[Final Pageant Score]
    
    FinalScore --> Rank[Assign Rank]
    Rank --> Broadcast[Broadcast Updates]
```

### Calculation Steps
1.  **Judge Input**: Judge submits a score for a specific criteria.
2.  **Level 1 (Judge-Contestant)**: All criteria scores from one judge for one contestant are weighted and averaged.
3.  **Level 2 (Contestant-Round)**: The weighted scores from all judges for that contestant in that round are averaged.
4.  **Level 3 (Final Score)**: The round scores for that contestant are weighted by round importance to produce the final score.
