# AniCore

A PHP web application for browsing, searching, and managing anime titles. Users can create watchlists and view anime details, while administrators can add and edit content.

## Overview

AniCore is a full-stack anime database management system built with PHP and MySQL. The application provides functionality for both end users and administrators to interact with anime content through a web interface.

## Features

**User Features:**
- Browse anime database
- Search by title and genre
- Create and manage personal watchlists
- View detailed anime information
- Secure authentication system

**Administrator Features:**
- Add new anime titles
- Edit existing anime entries
- Delete anime content
- Manage user accounts
- Handle contact form submissions

## Technologies

- PHP (86.8%) - Server-side logic and backend
- JavaScript (8.0%) - Frontend interactions
- CSS (5.2%) - Styling and layout
- MySQL - Database management
- HTML - Page structure

## Project Structure

```
AniCore/
├── actions/              # Backend logic and API endpoints
├── config/              # Configuration files
├── css/                 # Stylesheets
├── database/            # Database schema
├── images/              # Image assets
├── includes/            # Reusable components
├── js/                  # JavaScript files
├── index.php            # Homepage
├── login.php            # Authentication
├── admin.php            # Admin dashboard
├── add_anime.php        # Add anime (admin)
├── edit_anime.php       # Edit anime (admin)
├── delete_anime.php     # Delete anime (admin)
├── anime_detail.php     # Anime details page
├── contact.php          # Contact form
└── logout.php           # User logout
```

## Installation

**Requirements:**
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx web server

**Setup:**

1. Clone the repository:
```bash
git clone https://github.com/Madan-21/AniCore.git
cd AniCore
```

2. Create MySQL database and import schema from `database/` folder

3. Configure database connection in `config/` files:
```
DB_HOST=localhost
DB_NAME=anicore
DB_USER=your_username
DB_PASS=your_password
```

4. Configure web server to point to AniCore directory

5. Access at `http://localhost/AniCore`

## Security

- Password hashing for user credentials
- SQL injection prevention
- XSS protection
- Session-based authentication
- Role-based access control

## User Roles

**Regular Users:**
- Browse and search anime
- Manage watchlists
- View anime details
- Submit contact forms

**Administrators:**
- All user permissions
- Content management (add/edit/delete)
- User account management
- Contact message handling

## Future Development

- User rating and review system
- Recommendation engine
- REST API development
- Advanced search filters
- Multi-language support
- Export functionality

## Contributing

Fork the repository and submit pull requests for improvements.

## License

MIT License

## Author

**Madan Pandey**
- GitHub: [@Madan-21](https://github.com/Madan-21)
- Email: pmadan466@gmail.com
