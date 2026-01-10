# AniCore 🎌

A comprehensive PHP web application for browsing, searching, and managing anime titles. AniCore provides users with an intuitive platform to create watchlists, view detailed anime information, and allows administrators to efficiently add or edit anime content.

## 📋 Overview

AniCore is a full-featured anime management system built with PHP and MySQL. Whether you're an anime enthusiast looking to track your watchlist or an administrator managing a large anime database, AniCore provides all the tools you need in a clean, user-friendly interface.

## ✨ Features

### For Users
- **Browse Anime**: Explore a comprehensive database of anime titles
- **Advanced Search**: Find anime by title, genre, or other criteria
- **Watchlist Management**: Create and manage personal watchlists
- **Detailed Information**: View comprehensive details about each anime
- **User Authentication**: Secure login and account management

### For Administrators
- **Add Anime**: Easily add new anime titles to the database
- **Edit Content**: Update existing anime information
- **Delete Entries**: Remove outdated or incorrect anime entries
- **Manage Users**: Oversee user accounts and permissions
- **Contact Management**: Handle user messages and inquiries

## 🛠️ Technologies Used

- **PHP** (86.8%): Server-side logic and backend functionality
- **JavaScript** (8.0%): Dynamic frontend interactions
- **CSS** (5.2%): Styling and responsive design
- **MySQL**: Database management
- **HTML**: Page structure and content

## 📁 Project Structure

```
AniCore/
│
├── actions/              # Backend actions and API endpoints
├── config/              # Configuration files
├── css/                 # Stylesheets
├── database/            # Database schema and migrations
├── images/              # Image assets
├── includes/            # Reusable PHP components
├── js/                  # JavaScript files
│
├── index.php            # Homepage
├── login.php            # User authentication
├── admin.php            # Admin dashboard
├── add_anime.php        # Add new anime (admin)
├── edit_anime.php       # Edit anime details (admin)
├── delete_anime.php     # Delete anime entries (admin)
├── admin_add_anime.php  # Admin anime addition interface
├── anime_detail.php     # Detailed anime information page
├── contact.php          # Contact page
├── contact_messages.php # View contact messages (admin)
├── logout.php           # User logout
└── forgot_password.php  # Password recovery
```

## 🚀 Getting Started

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- phpMyAdmin (optional, for database management)

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/Madan-21/AniCore.git
cd AniCore
```

2. **Set up the database**
   - Create a new MySQL database
   - Import the database schema from the `database/` folder
   - Update database credentials in `config/` files

3. **Configure the application**
   - Open `config/` files
   - Update database connection settings
   - Set appropriate file permissions

4. **Configure your web server**
   - Point your web server document root to the AniCore directory
   - Ensure `.htaccess` file is enabled (for Apache)

5. **Access the application**
   - Open your browser and navigate to `http://localhost/AniCore`
   - Default admin credentials should be created during database setup

### Database Configuration

Update `login_credentials.txt` or your config file with:
```
DB_HOST=localhost
DB_NAME=anicore
DB_USER=your_username
DB_PASS=your_password
```

## 👥 User Roles

### Regular Users
- Browse and search anime
- Create and manage watchlists
- View anime details
- Contact administrators

### Administrators
- All user permissions
- Add new anime titles
- Edit existing anime
- Delete anime entries
- Manage user accounts
- View and respond to contact messages

## 🔐 Security Features

- Secure password hashing
- SQL injection prevention
- XSS protection
- Session management
- Password recovery system
- Admin-only access controls

## 📱 Features in Detail

### Anime Management
- Comprehensive anime information (title, genre, episodes, rating, etc.)
- Image upload and management
- Category and genre organization
- Search and filter functionality

### User Experience
- Responsive design for mobile and desktop
- Intuitive navigation
- Fast search capabilities
- Clean and modern interface

### Admin Panel
- Centralized dashboard
- Easy content management
- User management tools
- Message inbox for contact forms

## 🔮 Future Enhancements

- [ ] User reviews and ratings
- [ ] Recommendation system based on watch history
- [ ] Social features (friends, sharing watchlists)
- [ ] Email notifications for new anime additions
- [ ] Advanced filtering options
- [ ] API for mobile app integration
- [ ] Multi-language support
- [ ] Dark mode theme
- [ ] Export watchlist functionality

## 🐛 Known Issues

- Check the [Issues](https://github.com/Madan-21/AniCore/issues) page for current bugs and feature requests

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a new branch (`git checkout -b feature/YourFeature`)
3. Commit your changes (`git commit -m 'Add some feature'`)
4. Push to the branch (`git push origin feature/YourFeature`)
5. Open a Pull Request

## 📝 License

This project is open source and available under the [MIT License](LICENSE).

## 👤 Author

**Madan Pandey**
- GitHub: [@Madan-21](https://github.com/Madan-21)

## 🙏 Acknowledgments

- Anime data sources and APIs
- The anime community for inspiration
- Contributors and testers

## 📞 Support

If you encounter any issues or have questions:
- Open an issue on GitHub
- Use the contact form in the application
- Check existing documentation

---

⭐ If you find this project useful, please consider giving it a star!

## 📸 Screenshots

*Add screenshots of your application here to showcase the interface*

---

Made with ❤️ for anime fans everywhere
