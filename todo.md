# Real-time Scoring Implementation Plan

- [x] **Backend: Create Broadcasting Event**
    - [x] Generate a new event `ScoreUpdated` using `php artisan make:event ScoreUpdated`.
    - [x] Implement `ShouldBroadcast` interface on the event.
    - [x] Define the broadcast channel (e.g., a private channel for the pageant `pageant.{pageantId}`).
    - [x] Define the data to be broadcast with the event (e.g., score, contestant, judge, criteria, round info).

- [x] **Backend: Dispatch Event**
    - [x] Modify `JudgeController@submitScores` to dispatch the `ScoreUpdated` event after successfully saving scores.

- [x] **Frontend: Configure Broadcasting**
    - [x] Ensure Laravel Echo is configured correctly in `resources/js/bootstrap.js`.
    - [x] Ensure Pusher (or another driver) is installed and configured. I can check `composer.json` and `.env`.

- [x] **Frontend (Tabulator): Listen for Events**
    - [x] Identify the Tabulator's scoring component (likely `resources/js/Pages/Tabulator/Scores.vue`).
    - [x] In the component, use Laravel Echo to join the pageant's channel.
    - [x] Listen for the `ScoreUpdated` event.
    - [x] When the event is received, update the local data to reflect the new score in real-time. This might involve updating an array of scores.

- [ ] **Testing**
    - [ ] Manually test the flow: one user as a judge, another as a tabulator.
    - [ ] Submit a score as the judge and verify it appears on the tabulator's screen without a refresh.
