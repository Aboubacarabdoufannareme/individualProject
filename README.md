# DigiCareer Niger
DigiCareer Niger is a web-based recruitment platform designed to connect job seekers (candidates) with employers across Niger.  
The system digitizes the recruitment process by allowing candidates to create professional profiles and employers to post jobs, search candidates, and manage applications efficiently.

## Project Overviow 

DigiCareer Niger aims to reduce unemployment challenges by providing a centralized online platform where:
- Candidates can showcase their skills, education, and experience
- Candidate can build a CV and print it to upload into profile if does have one
- Employers can easily find qualified candidates
- Job applications are managed digitally and securely
  
The website focuses on simplicity , accessibility, and relevance to the Niger Job market.

## User Roles

### Candidate
- Register and login securely
- Edit personal profile
- Build CV using CV builder
-upload CV, diplomas, and documents
-Apply for jobs
-View job listings
-Track applications
- Accept/ reject invitation for employer
  
## Employer

- Register and login securely
- Edit company profile
- Search candidate using live search
- Manage job applications(Approve, reject, pending )
- Post job
- view candidate profiles
- send invitation to candidate

## Technologies used

### Frontend 

- HTLM5
- CSS
- Javascript (AJAX)

  ### Backend
  - PHP (PDO)
  - Session based authentication
  ### Database
  - MySQL
  ### Tools 
  - GitHub
  - Apache (XAMPP_
  - Live Hosting Server

  ## Security Features
  - Password hashing using password_hash()
  - Session-based access control
  - role-based authorization (Candidate/ Employer)
 
  ## Testing
  - Manual testing of all features
  - Frontend form validation
  - Backend validation using PHP
  - PHPUnit testing: Not emplemented

  ## Live Application 

  http://169.239.251.102:341/~fannareme.abdou/individualProject/

  ## Video D emonstration
  The video demonstrates:
  - Login and Registration ( Candidate and Employer)
  - Candidate dashboard :
    bluiding CV,
    Edit profile ,
    upload CV,
    apply job,
    accept and reject job,
    hidden profile from the candidate list.
  - Employer dashboard :
    Job posting
    candidate search
    profile management
    inviting candidate
    manage applications

  ## Database

  The database SQL file is included in this repository

  It contains:
  - candidates
  - employers
  - jobs
  - jobs_applications
  - documents
  - user_tokens
  - rest_password
