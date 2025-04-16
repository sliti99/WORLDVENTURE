# Blog Feature - Verification Guide

This guide will help you verify that all aspects of the blog feature are working correctly.

## User Roles & Permissions

The blog system supports three user roles with different permissions:

| Role    | View Posts | React | Comment | Create/Edit Posts | Delete Comments |
|---------|-----------|-------|---------|------------------|----------------|
| Visitor | ✅        | ❌    | ❌      | ❌               | ❌             |
| User    | ✅        | ✅    | ✅      | ❌               | ❌             |
| Admin   | ✅        | ✅    | ✅      | ✅               | ✅             |

## Testing Different Roles

To test different user roles, modify the BlogController.php file:

```php
// In the __construct() method, uncomment one of these lines:
$_SESSION['user_role'] = 'visitor'; // Default role
// $_SESSION['user_role'] = 'user';    // Regular logged-in user
// $_SESSION['user_role'] = 'admin';   // Administrator
```

## Features to Test

1. **Viewing Posts**
   - All users should be able to see the blog posts list and individual posts.
   - Verify that post details, reactions count, and comments are displayed correctly.

2. **Reactions**
   - Only logged-in users (user, admin) should be able to react to posts and comments.
   - Verify that clicking the "React" button increases the reaction count.

3. **Comments**
   - Only logged-in users can add comments.
   - Verify that the comment form is hidden for visitors.
   - Test comment form validation (should not allow empty comments).
   - Verify that new comments appear on the post details page.

4. **Post Management (Admin Only)**
   - Verify that the "Add Post" form appears only for admins.
   - Test creating a new post.
   - Test editing an existing post.
   - Test deleting a post (should also delete related comments).

5. **Comment Management (Admin Only)**
   - Verify that delete comment buttons appear only for admins.
   - Test deleting a comment.

## Common Issues & Solutions

1. **"Access Denied" Messages**
   - Ensure you're using the correct user role for the operation.
   - Check controller.php to verify role-permission settings.

2. **Missing Reactions**
   - Check that the "reactions" column exists in both posts and comments tables.
   - Verify that incrementPostReaction() and incrementCommentReaction() methods are working.

3. **Form Submissions Not Working**
   - Verify that form actions point to the controller.php file.
   - Check that proper action parameters are included in forms.

4. **JavaScript Validation Issues**
   - Ensure the validateForm(), validateCommentForm(), and validateEditForm() functions are correctly implemented.
   - Check browser console for JavaScript errors.

## MVC Pattern Verification

This blog implementation follows the MVC pattern:
- **Models**: Handle database operations (blog part/models/model.php)
- **Views**: Display the interface (blog part/views/*)
- **Controllers**: Process user inputs and coordinate models/views (blog part/controllers/controller.php)

Maintain this separation when making changes to ensure the application remains maintainable.
