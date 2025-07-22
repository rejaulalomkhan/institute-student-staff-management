# Institute Student and Staff Management Plugin v2.0.0

A comprehensive WordPress plugin for managing students and staff in educational institutes. This modernized version includes enhanced features, improved security, responsive design, and REST API support.

## 🚀 New Features in v2.0.0

### Modern Architecture
- **Namespaced Classes**: Clean, organized code structure with PSR-4 autoloading
- **Enhanced Security**: Improved nonces, capability checks, and input validation
- **Modern PHP**: Compatible with PHP 7.4+ with type declarations and modern practices
- **WordPress Standards**: Follows current WordPress coding standards and best practices

### Enhanced User Interface
- **Responsive Design**: Mobile-first approach with CSS Grid and Flexbox
- **Modern Styling**: Clean, professional design with CSS custom properties
- **Dark Mode Support**: Automatic dark mode based on user preferences
- **Improved Admin Interface**: Enhanced meta boxes and list tables with better UX

### Advanced Features
- **REST API Endpoints**: Full REST API support for external integrations
- **Dashboard Analytics**: Real-time statistics and charts
- **Import/Export**: CSV import/export functionality
- **Email Notifications**: Configurable notification system
- **Advanced Shortcodes**: Multiple display options and filtering
- **Custom Taxonomies**: Enhanced class and department management

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## 🛠 Installation

1. **Download** the plugin files
2. **Upload** to `/wp-content/plugins/institute-student-staff-management/`
3. **Activate** the plugin through the WordPress admin panel
4. **Configure** settings under Institute → Settings

## 🎯 Core Features

### Student Management
- Complete student profiles with personal, academic, and contact information
- Student ID generation and management
- Class assignment and batch tracking
- Academic session management
- Status tracking (Active, Inactive, Graduated, Suspended, Dropped)

### Staff Management
- Comprehensive staff profiles with employment details
- Department and role assignment
- Salary and employment type tracking
- Qualification and experience management
- Contact information and emergency contacts

### Custom Post Types
- **Students**: Full profile management with custom fields
- **Staff**: Complete employment and personal information

### Custom Taxonomies
- **Student Classes**: Hierarchical class organization
- **Staff Departments**: Department structure management
- **Student Batches**: Batch/year group management
- **Staff Roles**: Role-based staff categorization

## 🎨 Shortcodes

### Students List
```php
[students_list class="class-10" limit="12" columns="3" show_photo="yes" style="grid"]
```

**Attributes:**
- `class`: Filter by specific class
- `batch`: Filter by batch
- `limit`: Number of students to display (default: 12)
- `columns`: Grid columns (1-6, default: 3)
- `show_photo`: Show/hide photos (yes/no)
- `show_class`: Show/hide class info (yes/no)
- `show_session`: Show/hide session info (yes/no)
- `show_branch`: Show/hide branch info (yes/no)
- `show_contact`: Show/hide contact info (yes/no)
- `orderby`: Sort order (title, date, menu_order)
- `order`: ASC or DESC
- `status`: Filter by status (active, inactive, etc.)
- `style`: Display style (grid, list, table)

### Staff List
```php
[staff_list department="administration" limit="8" columns="2" show_email="yes"]
```

**Attributes:**
- `department`: Filter by department
- `role`: Filter by staff role
- `limit`: Number of staff to display (default: 12)
- `columns`: Grid columns (1-6, default: 3)
- `show_photo`: Show/hide photos (yes/no)
- `show_position`: Show/hide position (yes/no)
- `show_department`: Show/hide department (yes/no)
- `show_phone`: Show/hide phone (yes/no)
- `show_email`: Show/hide email (yes/no)
- `orderby`: Sort order
- `order`: ASC or DESC
- `status`: Filter by status
- `style`: Display style (grid, list, table)

### Institute Directory
```php
[institute_directory show_search="yes" show_filters="yes"]
```

**Attributes:**
- `show_search`: Enable search functionality (yes/no)
- `show_filters`: Show filter options (yes/no)
- `show_students`: Include students (yes/no)
- `show_staff`: Include staff (yes/no)
- `style`: tabs or sections

### Institute Statistics
```php
[institute_stats show="students,staff,classes,departments" style="cards"]
```

**Attributes:**
- `show`: Comma-separated list of statistics to display
- `style`: Display style (cards, counters, chart)

## 🎛 Admin Interface

### Dashboard
- Real-time statistics overview
- Quick action buttons
- Recent activity feed
- Analytics charts

### Settings Page
- Plugin configuration options
- Notification preferences
- Display settings
- Import/export options

### Enhanced List Tables
- Sortable columns
- Thumbnail previews
- Status indicators
- Quick edit options
- Bulk actions

## 🔌 REST API

The plugin provides REST API endpoints for external integrations:

### Students Endpoint
```
GET /wp-json/wp/v2/students
POST /wp-json/wp/v2/students
GET /wp-json/wp/v2/students/{id}
PUT /wp-json/wp/v2/students/{id}
DELETE /wp-json/wp/v2/students/{id}
```

### Staff Endpoint
```
GET /wp-json/wp/v2/staff
POST /wp-json/wp/v2/staff
GET /wp-json/wp/v2/staff/{id}
PUT /wp-json/wp/v2/staff/{id}
DELETE /wp-json/wp/v2/staff/{id}
```

### Taxonomies
```
GET /wp-json/wp/v2/student-classes
GET /wp-json/wp/v2/staff-departments
GET /wp-json/wp/v2/student-batches
GET /wp-json/wp/v2/staff-roles
```

## 📊 Import/Export

### Supported Formats
- CSV files for bulk import/export
- Excel-compatible formatting
- UTF-8 encoding support

### Import Features
- Drag-and-drop file upload
- Data validation and error reporting
- Preview before import
- Batch processing for large files

### Export Features
- Filtered exports by class, department, or status
- Custom field selection
- Multiple format options

## 🔧 Developer Features

### Hooks and Filters
```php
// Customize student meta fields
add_filter('institute_management_student_meta_fields', 'custom_student_fields');

// Modify staff display
add_action('institute_management_staff_display', 'custom_staff_display');

// Custom notification triggers
add_action('institute_management_student_created', 'custom_notification');
```

### Template Override
Place custom templates in your theme:
```
your-theme/
  institute-management/
    single-student.php
    archive-student.php
    shortcode-students-list.php
```

## 🎨 Styling and Customization

### CSS Classes
The plugin uses a comprehensive set of CSS classes for easy customization:

```css
/* Main containers */
.institute-students-wrapper
.institute-staff-wrapper
.institute-directory-wrapper

/* Card styles */
.institute-student-card
.institute-staff-card
.institute-card-photo
.institute-card-content

/* Grid layouts */
.institute-columns-1 through .institute-columns-6

/* Status indicators */
.institute-status-active
.institute-status-inactive
.institute-status-graduated
```

### CSS Custom Properties
```css
:root {
  --institute-primary: #2563eb;
  --institute-secondary: #7c3aed;
  --institute-radius: 8px;
  --institute-spacing: 1rem;
}
```

## ⚙️ Configuration

### Basic Settings
1. Navigate to **Institute → Settings**
2. Configure notification preferences
3. Set display options
4. Save settings

### Advanced Configuration
```php
// In wp-config.php or functions.php
define('INSTITUTE_MANAGEMENT_CACHE_DURATION', 3600);
define('INSTITUTE_MANAGEMENT_UPLOAD_SIZE', '5MB');
```

## 🔒 Security Features

- **Nonce Verification**: All forms include nonce fields
- **Capability Checks**: Proper user permission validation
- **Input Sanitization**: All input is sanitized and validated
- **Output Escaping**: All output is properly escaped
- **SQL Injection Prevention**: Uses prepared statements

## 🌐 Internationalization

The plugin is fully internationalized and translation-ready:
- Text domain: `institute-management`
- POT file included for translators
- RTL language support
- Date/time localization

## 📱 Responsive Design

- Mobile-first approach
- Responsive grid layouts
- Touch-friendly interfaces
- Optimized for all screen sizes

## 🧪 Testing

### Browser Support
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

### WordPress Compatibility
- WordPress 5.0+
- Multisite compatible
- Gutenberg ready

## 🚀 Performance

- **Optimized Database Queries**: Efficient data retrieval
- **Caching Support**: Built-in caching mechanisms
- **Lazy Loading**: Images loaded on demand
- **Minified Assets**: Compressed CSS and JavaScript

## 📞 Support

### Documentation
- Comprehensive inline documentation
- Code examples and snippets
- Video tutorials (coming soon)

### Community
- GitHub Issues for bug reports
- Feature requests welcome
- Community contributions encouraged

## 🔄 Migration from v1.0

The plugin automatically handles migration from version 1.0:
1. **Data Preservation**: All existing data is maintained
2. **Setting Migration**: Settings are automatically updated
3. **Template Compatibility**: Old templates continue to work
4. **Shortcode Compatibility**: Existing shortcodes remain functional

## 📝 Changelog

### Version 2.0.0
- Complete plugin rewrite with modern architecture
- Enhanced security and performance
- New REST API endpoints
- Improved admin interface
- Advanced shortcodes and filtering
- Responsive design implementation
- Import/export functionality
- Dashboard analytics
- Email notification system

### Version 1.0.0
- Initial release
- Basic student and staff management
- Simple shortcodes
- Basic admin interface

## 📄 License

GPL v2 or later - see LICENSE file for details.

## 👥 Contributors

- **Arman Azij** - Lead Developer
- Community contributors welcome!

## 🙏 Acknowledgments

- WordPress community for best practices
- Educational institutions for feedback
- Open source contributors

---

**Made with ❤️ for educational institutions worldwide** 