# Pageant Tabulation System - User Guide

This guide provides comprehensive instructions for setting up and deploying the Pageant Tabulation System. Whether you're installing the system on a local development environment for testing or deploying it to a production server, this document outlines the necessary steps, requirements, and troubleshooting procedures to ensure a successful implementation.

---

## System Requirements

### Server Requirements
- **PHP** 8.2 or higher
- **Composer** 2.0+
- **MySQL** 5.7 or higher
- **Node.js** 18+ and NPM
- **Git** (optional, for version control)

### Recommended Extensions
- PHP Extensions: BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML
- PHP GD or Imagick (for image processing)

---

## Local Setup Instructions

### 1. Prepare Your Environment

- Install XAMPP, WAMP, Laragon, or a similar local development environment with PHP 8.2+
- Ensure PHP and MySQL services are running
- Create a new MySQL database named `pageant_tabulation`

### 2. Application Setup

- Extract the system files to your desired location (e.g., `C:\laragon\www\pageant-system`)
- Open terminal/command prompt in the project directory

### 3. Environment Configuration

Copy the example environment file and configure it:

```
copy .env.example .env
```

Edit the `.env` file with your database credentials:

```
APP_NAME="Pageant Tabulation System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pageant_tabulation
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Install Dependencies

Run the following commands in your terminal:

```
composer install
npm install
npm run build
```

### 5. Generate Application Key

```
php artisan key:generate
```

### 6. Database Setup

**Option A:** Run migrations and seeders (recommended for fresh install):

```
php artisan migrate --seed
```

**Option B:** Import the database file using phpMyAdmin or MySQL command:

```
mysql -u root -p pageant_tabulation < database.sql
```

### 7. Start Local Server

**Option A:** Simple server start:

```
php artisan serve
```

**Option B:** Full development environment (with real-time features):

```
npm run serve:dev
```

This starts Laravel server, WebSocket server (Reverb), Queue worker, and Vite development server.

Access the system at: **http://localhost:8000**

---

## Online/Production Setup

### 1. Server Requirements

- Web server (Apache/Nginx)
- PHP 8.2+
- MySQL 5.7+
- SSL certificate (required for WebSocket connections)
- Supervisor (for queue worker management)

### 2. Deployment Steps

**Step 1:** Upload all files to your web hosting using FTP/SFTP

**Step 2:** Create a MySQL database through hosting control panel

**Step 3:** Import the database file

**Step 4:** Configure `.env` file with production settings:

```
APP_NAME="Pageant Tabulation System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_HOST=your-database-host
DB_DATABASE=your-database-name
DB_USERNAME=your-database-user
DB_PASSWORD=your-database-password

REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=your-domain.com
REVERB_PORT=443
REVERB_SCHEME=https
```

**Step 5:** Set proper permissions (Linux/Mac):

```
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 3. Production Optimization

Run these commands to optimize the application:

```
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

### 4. Queue Worker Setup

Configure Supervisor to manage the queue worker. Create `/etc/supervisor/conf.d/pageant-worker.conf`:

```
[program:pageant-worker]
command=php /path/to/project/artisan queue:work --tries=3
directory=/path/to/project
user=www-data
autostart=true
autorestart=true
```

---

## Troubleshooting Common Issues

### 1. Database Connection Error

- Verify database credentials in `.env` file
- Ensure MySQL service is running
- Check database user privileges
- Run `php artisan config:clear` after changing `.env`

### 2. 500 Server Error

- Check storage folder permissions
- Verify `.env` file exists and is properly configured
- Review Laravel error logs in `storage/logs/laravel.log`
- Ensure all PHP extensions are installed

### 3. White Screen/Blank Page

- Enable error reporting in `php.ini`
- Check PHP version compatibility
- Clear application cache:

```
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### 4. Assets Not Loading

- Run `npm run build` to compile assets
- Check if the symbolic link exists: `php artisan storage:link`
- Verify `APP_URL` in `.env` matches your domain

### 5. Real-time Updates Not Working

- Ensure WebSocket server (Reverb) is running
- Check `REVERB_*` configuration in `.env`
- For production, ensure SSL is properly configured
- Verify firewall allows WebSocket port

---

## Running and Navigation of the System

The user manual is designed to offer guidance to individuals using the system. This section demonstrates how to perform specific tasks organized by user role.

---

## User Roles Overview

| Role | Primary Responsibility | Access Level |
|------|------------------------|--------------|
| **Admin** | System oversight and user management | Full system access |
| **Organizer** | Pageant creation and configuration | Pageant management |
| **Tabulator** | Live scoring operations and results | Scoring control |
| **Judge** | Contestant evaluation | Scoring interface |

---

## Admin Portal

### Process: Login Page

**Description:** Allows the administrator to log into the system.

**Procedures:**
1. Navigate to the system URL
2. Enter username and password
3. Click the Login button
4. You will be redirected to the Admin Dashboard

---

### Process: Admin Dashboard

**Description:** The central hub for system administration.

**Features Available:**
- View system statistics and activity overview
- Quick access to pending approvals
- Monitor active pageants
- View recent audit logs

---

### Process: Managing Organizers

**Description:** Create and manage organizer accounts.

**Procedures to Create an Organizer:**
1. Navigate to Admin → Users → Organizers
2. Click "Create New Organizer"
3. Fill in the required information:
   - Full Name
   - Email Address
   - Username
   - Temporary Password
4. Click "Create Organizer"
5. The organizer receives a verification email
6. Once verified, the account becomes active

---

### Process: Approving Pageants

**Description:** Review and approve pageants created by organizers.

**Procedures:**
1. Navigate to Admin → Pageants → Pending Approvals
2. Click on a pageant to view details
3. Review the pageant configuration:
   - Event details (name, date, venue)
   - Scoring criteria and rounds
   - Contestant information
4. Click "Approve" or "Reject" with notes
5. The organizer is notified of the decision

---

### Process: Viewing Audit Logs

**Description:** Monitor system activities for security and compliance.

**Procedures:**
1. Navigate to Admin → Audit Log
2. Filter logs by:
   - Date range
   - User
   - Action type
3. Click on any entry to view detailed information
4. Export logs as needed for reporting

---

## Organizer Portal

### Process: Creating a New Pageant

**Description:** Set up a new pageant event with all configurations.

**Procedures:**
1. Navigate to Organizer → My Pageants
2. Click "Create New Pageant"
3. Enter pageant details:
   - Pageant Name
   - Description
   - Event Date
   - Venue and Location
   - Cover Image and Logo (optional)
4. Configure scoring system:
   - Select scoring type (Percentage, 1-10, 1-5, Points)
   - Set number of required judges
5. Click "Create Pageant"
6. The pageant is submitted for admin approval

---

### Process: Managing Criteria and Rounds

**Description:** Define scoring criteria and competition rounds.

**Procedures:**
1. Navigate to your pageant → Rounds Management
2. Click "Add Round" to create a new round:
   - Enter round name (e.g., "Swimwear", "Evening Gown", "Q&A")
   - Set round type (Semi-Final, Final, Casual)
   - Assign weight percentage
3. For each round, add criteria:
   - Click "Add Criteria"
   - Enter criteria name (e.g., "Poise", "Confidence")
   - Set weight and score range
4. Save all changes

---

### Process: Registering Contestants

**Description:** Add contestants to the pageant.

**Procedures:**
1. Navigate to your pageant → Contestants Management
2. Click "Add Contestant"
3. Enter contestant information:
   - Contestant Number
   - Full Name
   - Origin/Representation
   - Age
   - Gender
   - Biography (optional)
4. Upload contestant photos
5. Click "Save Contestant"
6. Repeat for all contestants

**For Pair Contestants:**
1. Select "Add Pair" instead
2. Enter details for both members
3. The system automatically links them as a pair

---

### Process: Assigning Tabulators

**Description:** Assign staff to manage the scoring process.

**Procedures:**
1. Navigate to your pageant → Judges & Tabulators
2. Click "Create Tabulator Account"
3. Enter tabulator details:
   - Full Name
   - Username
   - Password
4. Click "Create"
5. Share the credentials securely with the tabulator

---

### Process: Submitting for Approval

**Description:** Submit the configured pageant for admin review.

**Procedures:**
1. Ensure all setup is complete:
   - At least one round with criteria
   - Contestants registered
   - Tabulator assigned
2. Review the pageant summary
3. Click "Submit for Approval"
4. Wait for admin notification

---

## Tabulator Portal

### Process: Managing Judges

**Description:** Create and assign judges to the pageant.

**Procedures:**
1. Navigate to Tabulator Dashboard → Judges
2. Click "Create Judge Account"
3. Enter judge information:
   - Full Name
   - Username
   - Password
4. Click "Create Judge"
5. Share credentials with the judge before the event

---

### Process: Controlling Rounds

**Description:** Manage which round is active for scoring.

**Procedures:**
1. Navigate to Tabulator Dashboard → Round Management
2. View all available rounds
3. Click "Set as Current" on the desired round
4. Click "Unlock" to enable scoring for that round
5. Notify judges that scoring is open

---

### Process: Monitoring Scores

**Description:** View real-time scoring progress.

**Procedures:**
1. Navigate to Tabulator Dashboard → Scores
2. Select the active round
3. View the scoring grid showing:
   - Contestants vs. Judges
   - Submitted scores (highlighted)
   - Missing scores (empty cells)
4. Click on any cell to view score details
5. Monitor the progress bar for completion status

---

### Process: Locking Rounds

**Description:** Finalize scoring for a round.

**Procedures:**
1. Ensure all judges have submitted scores
2. Navigate to Round Management
3. Click "Lock Round" on the completed round
4. Confirm the action
5. Scores are now finalized and cannot be changed

---

### Process: Generating Results

**Description:** View and print competition results.

**Procedures:**
1. Navigate to Tabulator Dashboard → Results
2. Select the round or overall results
3. View the ranking table showing:
   - Contestant rankings
   - Total scores
   - Individual judge scores
4. Click "Print Results" for official documentation
5. Export as PDF if needed

---

### Process: Managing Contestant Back-outs

**Description:** Handle contestants who withdraw during competition.

**Procedures:**
1. Navigate to the Scores page
2. Find the contestant who backed out
3. Click the contestant's action menu
4. Select "Mark as Backed Out"
5. Confirm the action
6. The contestant is excluded from rankings

---

## Judge Portal

### Process: Accessing the Scoring Interface

**Description:** Log in and access the scoring page.

**Procedures:**
1. Navigate to the system URL
2. Enter your username and password
3. Click Login
4. You are redirected to the Judge Dashboard
5. Select your assigned pageant (if multiple)

---

### Process: Scoring Contestants

**Description:** Submit scores for contestants in the active round.

**Procedures:**
1. From the Judge Dashboard, click "Start Scoring"
2. Select the active round (shown as current)
3. View the contestant list
4. For each contestant:
   a. Click on the contestant card
   b. View contestant details and photos
   c. Enter scores for each criteria
   d. Click "Submit Score"
5. Continue until all contestants are scored
6. Review your submitted scores in the summary

---

### Process: Viewing Score Summary

**Description:** Review all scores you have submitted.

**Procedures:**
1. Navigate to Judge Dashboard → Score Summary
2. Select the round to view
3. See the complete list of your submitted scores
4. Scores are displayed with:
   - Contestant name
   - Criteria scores
   - Total score
5. Contact the tabulator if corrections are needed (before round is locked)

---

### Process: Comparing Contestants

**Description:** Compare scores between contestants.

**Procedures:**
1. Navigate to the round scoring page
2. Click "Comparison View"
3. View contestants side-by-side
4. Compare scores across criteria
5. Adjust scores as needed before final submission

---

## Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Tab` | Move to next input field |
| `Enter` | Submit/Confirm action |
| `Esc` | Close modal/Cancel action |

---

## Best Practices

### For Organizers
- Complete all pageant setup before the event day
- Test the scoring system with sample data
- Ensure all contestants have photos uploaded
- Verify criteria weights add up to 100%

### For Tabulators
- Create all judge accounts before the event
- Have backup login credentials ready
- Monitor scores in real-time during the event
- Lock rounds immediately after completion

### For Judges
- Familiarize yourself with criteria before scoring
- Score all contestants in one session if possible
- Double-check scores before submission
- Contact tabulator immediately if issues arise

---

## Security Recommendations

1. **Strong Passwords:** Use complex passwords for all accounts
2. **Regular Backups:** Backup the database before each event
3. **Session Security:** Log out when not actively using the system
4. **Access Control:** Only share credentials with authorized personnel
5. **Audit Monitoring:** Regularly review audit logs for suspicious activity

---

## Support and Contact

For technical support or questions about the system:

1. Check the troubleshooting section above
2. Review error logs in `storage/logs/laravel.log`
3. Contact your system administrator

---

## Appendix: Database Backup Procedures

### Creating a Backup

```
mysqldump -u root -p pageant_tabulation > backup_YYYYMMDD.sql
```

### Restoring from Backup

```
mysql -u root -p pageant_tabulation < backup_YYYYMMDD.sql
```

---

## Appendix: Quick Start Checklist

### Pre-Event Setup
- [ ] System installed and accessible
- [ ] Admin account configured
- [ ] Organizer accounts created
- [ ] Pageant created and approved
- [ ] Contestants registered
- [ ] Criteria and rounds configured
- [ ] Tabulator assigned
- [ ] Judge accounts created

### Event Day
- [ ] All users can log in
- [ ] Current round is set and unlocked
- [ ] Judges have access to scoring
- [ ] Real-time updates are working
- [ ] Printer is ready for results

### Post-Event
- [ ] All rounds locked
- [ ] Results printed and verified
- [ ] Database backed up
- [ ] Pageant marked as completed

---

*Document Version: 1.0*
*Last Updated: January 2026*
