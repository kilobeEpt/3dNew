# Coding Standards

This document outlines the coding standards and best practices for this project.

## PHP Standards

### PSR Standards

This project follows the PHP Standards Recommendations (PSR):

- **PSR-1**: Basic Coding Standard
- **PSR-4**: Autoloading Standard
- **PSR-12**: Extended Coding Style Guide

### General Guidelines

#### File Structure

- PHP files MUST use only `<?php` tags
- PHP files MUST use the Unix LF (linefeed) line ending
- PHP files MUST end with a single blank line
- The closing `?>` tag MUST be omitted from files containing only PHP

#### Naming Conventions

**Classes**
- Use `PascalCase` (StudlyCase)
- Example: `UserController`, `DatabaseConnection`

**Methods and Functions**
- Use `camelCase`
- Example: `getUserById()`, `validateEmail()`

**Variables**
- Use `camelCase`
- Example: `$userData`, `$isActive`

**Constants**
- Use `UPPER_CASE` with underscores
- Example: `DB_HOST`, `MAX_LOGIN_ATTEMPTS`

**Namespaces**
- Follow PSR-4 structure
- Match directory structure
- Example: `App\Controllers\Admin\DashboardController`

#### Code Style

**Indentation**
- Use 4 spaces (no tabs)

**Line Length**
- Soft limit: 80 characters
- Hard limit: 120 characters

**Braces**
- Opening braces for classes and methods go on the next line
- Opening braces for control structures go on the same line

```php
class Example
{
    public function method()
    {
        if ($condition) {
            // code
        }
    }
}
```

**Visibility**
- Always declare visibility for properties and methods
- Use `public`, `protected`, or `private`

```php
class Example
{
    private string $property;
    
    public function method(): void
    {
        // code
    }
}
```

**Type Declarations**
- Use type hints for parameters and return types when possible
- Use strict types: `declare(strict_types=1);`

```php
<?php

declare(strict_types=1);

namespace App\Example;

class Calculator
{
    public function add(int $a, int $b): int
    {
        return $a + $b;
    }
}
```

### Error Handling

- Use exceptions for error handling
- Don't suppress errors with `@`
- Log errors appropriately

```php
try {
    $result = $this->riskyOperation();
} catch (Exception $e) {
    $logger->error($e->getMessage());
    throw new RuntimeException('Operation failed', 0, $e);
}
```

### Database Queries

- Always use prepared statements
- Never concatenate user input into SQL
- Use meaningful variable names

```php
// Good
$stmt = $db->query('SELECT * FROM users WHERE id = ?', [$userId]);

// Bad
$query = "SELECT * FROM users WHERE id = " . $_GET['id'];
```

### Security Best Practices

1. **Input Validation**
   - Validate all user inputs
   - Use the Validator helper
   - Sanitize data before use

2. **Output Escaping**
   - Escape output in HTML contexts
   - Use `htmlspecialchars()` for user-generated content

3. **Authentication**
   - Never store passwords in plain text
   - Use `password_hash()` and `password_verify()`
   - Implement rate limiting for login attempts

4. **SQL Injection Prevention**
   - Always use prepared statements
   - Never trust user input

5. **XSS Prevention**
   - Escape all output
   - Use Content Security Policy headers

## JavaScript Standards

### General Guidelines

#### Naming Conventions

**Variables and Functions**
- Use `camelCase`
- Example: `getUserData()`, `isActive`

**Constants**
- Use `UPPER_CASE` with underscores
- Example: `API_BASE_URL`, `MAX_RETRIES`

**Classes**
- Use `PascalCase`
- Example: `UserManager`, `ApiClient`

#### Code Style

**Indentation**
- Use 4 spaces (no tabs)

**Semicolons**
- Always use semicolons

**Quotes**
- Use single quotes for strings
- Use template literals for string interpolation

```javascript
const name = 'John';
const greeting = `Hello, ${name}!`;
```

**Functions**
- Prefer arrow functions for callbacks
- Use async/await for asynchronous code

```javascript
// Arrow function
const square = (x) => x * x;

// Async function
async function fetchData() {
    const response = await fetch('/api/data');
    return response.json();
}
```

### API Requests

- Use the provided `api` helper
- Handle errors appropriately
- Show user feedback for long operations

```javascript
try {
    const data = await window.api.get('/users');
    console.log(data);
} catch (error) {
    console.error('Failed to fetch users:', error);
}
```

## CSS Standards

### General Guidelines

#### Naming Conventions

- Use kebab-case for class names
- Use meaningful, semantic names
- Avoid abbreviations

```css
/* Good */
.user-profile {}
.navigation-menu {}

/* Bad */
.usrProf {}
.nav-m {}
```

#### Organization

- Group related properties together
- Order properties logically (positioning, box model, typography, visual)

```css
.element {
    /* Positioning */
    position: relative;
    top: 0;
    
    /* Box Model */
    display: flex;
    width: 100%;
    padding: 1rem;
    margin: 0 auto;
    
    /* Typography */
    font-size: 1rem;
    line-height: 1.6;
    
    /* Visual */
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
}
```

#### Best Practices

- Avoid `!important` when possible
- Use relative units (rem, em) for scalability
- Keep specificity low
- Use CSS variables for repeated values

```css
:root {
    --primary-color: #667eea;
    --spacing-unit: 1rem;
}

.button {
    background: var(--primary-color);
    padding: var(--spacing-unit);
}
```

## Git Commit Messages

### Format

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting)
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

### Examples

```
feat(api): add user registration endpoint

Implemented POST /api/users endpoint with validation
and email verification.

Closes #123
```

```
fix(auth): resolve token validation issue

Fixed bug where expired tokens were being accepted.
Added proper expiration checking.
```

## Documentation

### Code Comments

- Write self-documenting code when possible
- Comment complex logic and algorithms
- Explain "why", not "what"
- Keep comments up to date

```php
// Good
// Using binary search for O(log n) performance on large datasets
$result = $this->binarySearch($array, $target);

// Bad
// Loop through array
foreach ($array as $item) {
    // code
}
```

### PHPDoc Blocks

Use PHPDoc for classes and methods:

```php
/**
 * Validates user input data
 *
 * @param array $data The data to validate
 * @param array $rules The validation rules
 * @return bool True if validation passes
 * @throws ValidationException If validation fails
 */
public function validate(array $data, array $rules): bool
{
    // implementation
}
```

## Testing

### Unit Tests

- Write tests for business logic
- Use descriptive test names
- Follow AAA pattern (Arrange, Act, Assert)

```php
public function testUserCanBeCreated(): void
{
    // Arrange
    $userData = ['name' => 'John', 'email' => 'john@example.com'];
    
    // Act
    $user = User::create($userData);
    
    // Assert
    $this->assertEquals('John', $user->name);
    $this->assertEquals('john@example.com', $user->email);
}
```

## Performance

### Best Practices

1. **Database**
   - Use indexes appropriately
   - Avoid N+1 queries
   - Use pagination for large datasets

2. **Caching**
   - Cache expensive operations
   - Implement appropriate cache invalidation

3. **API**
   - Implement rate limiting
   - Use appropriate HTTP caching headers
   - Return only necessary data

4. **Frontend**
   - Minify CSS and JavaScript
   - Optimize images
   - Use lazy loading where appropriate

## Code Review Checklist

- [ ] Code follows PSR standards
- [ ] Variables and functions are properly named
- [ ] Code is properly commented
- [ ] No security vulnerabilities
- [ ] Error handling is implemented
- [ ] Tests are included (if applicable)
- [ ] Documentation is updated
- [ ] No unnecessary code or debug statements

## Resources

- [PSR-1: Basic Coding Standard](https://www.php-fig.org/psr/psr-1/)
- [PSR-4: Autoloading Standard](https://www.php-fig.org/psr/psr-4/)
- [PSR-12: Extended Coding Style Guide](https://www.php-fig.org/psr/psr-12/)
- [PHP The Right Way](https://phptherightway.com/)
- [OWASP Security Guidelines](https://owasp.org/)
