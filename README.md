# HRMS: Human Resources Management System

  <img width="100%" loading="lazy" src="https://github.com/SamirPaulb/SamirPaulb/blob/main/assets/rainbow-superthin.webp" />
  
## Overview

The HRMS (Human Resource Management System) is a php-based application designed to manage employees, trainees, applicants, schedules, attendance, positions, interviews, payroll, corrective actions, and leave within an organization. It features multi-role authentication and authorization, detailed analytics, and comprehensive management tools.

## Features

- **Multi-role Authentication:**
- **Employee Management:**
- **Schedule Management:**
- **Position Management:** CRUD operations and assignment to employees
- **Statistics and Analytics:** Performance metrics and visualizations using Charts.js
- **User Management:** 

## Screenshots

## Installation

### Prerequisites

- ![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white) PHP >= 7.4
- ![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat&logo=mysql&logoColor=white) MySQL
- ![Composer](https://img.shields.io/badge/Composer-885630?style=flat&logo=composer&logoColor=white) Composer

### Setup

1. Clone the repository:
    ```sh
    git clone https://github.com/dfrgroup/hrms.git
    cd hrms
    ```
2. Install dependencies:
    ```sh
    composer install
    npm install
    npm run dev
    ```
3. Set Initial App Config:
Update the config/app.php app configuration file to the following:
    ```sh
    env => production,
    debug => false,  // Set to false in production
    ```

4. Migrate the database:

## Admin Dashboard

Admins can manage employees, trainees, schedules, positions, interviews, and vacations. They have access to detailed statistics and performance metrics.

## Moderator Dashboard

Moderators can activate or deactivate admin accounts and manage their own profiles.

## Database Schema

Key tables and their relationships:

- `employees`: Manages employee data, references positions and schedules
- `interviews`: Manages interview schedules, references users
- `positions`: Manages job positions
- `schedules`: Manages work schedules
- `users`: Stores user data with roles and permissions

## API Endpoints

### Authentication

- `POST /api/login`
- `POST /api/logout`

### Employees

- `GET /api/employees`
- `POST /api/employees`
- `PUT /api/employees/{id}`
- `DELETE /api/employees/{id}`

### Positions

- `GET /api/positions`
- `POST /api/positions`
- `PUT /api/positions/{id}`
- `DELETE /api/positions/{id}`


## Frontend

- ![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=flat&logo=bootstrap&logoColor=white) Uses Bootstrap for responsive design
- ![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=flat&logo=css3&logoColor=white) CSS for styling
- ![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black) JavaScript for dynamic functionality
- ![jQuery](https://img.shields.io/badge/jQuery-0769AD?style=flat&logo=jquery&logoColor=white) jQuery for AJAX requests
- ![AJAX](https://img.shields.io/badge/AJAX-0769AD?style=flat&logo=ajax&logoColor=white) AJAX for asynchronous operations
- ![Chart.js](https://img.shields.io/badge/Chart.js-F37826?style=flat&logo=chart-dot-js&logoColor=white) Charts.js for data visualizations
- ![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=flat&logo=html5&logoColor=white) HTML for markup


## Security

- Ensure correct configuration of environment variables.
- Regularly update dependencies to address security vulnerabilities.

## Contribution

1. Fork the repository
2. Create a new branch (`git checkout -b feature/your-feature`)
3. Commit your changes (`git commit -am 'Add some feature'`)
4. Push to the branch (`git push origin feature/your-feature`)
5. Create a new Pull Request

## License

This project is licensed under the GNU General Public License v3.0 License. See the [LICENSE](LICENSE) file for details.

## Contact

For questions or support, contact [anthony@dfrgrp.com](mailto:anthony@dfrgrp.com).

## Live Demo

Check out the live demo: 