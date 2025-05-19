Thank you for taking the time to complete this task. We appreciate your effort and look forward to reviewing your submission.

We would like you to create a basic Laravel API called **“Laravel Tech Task Demo API”**. The purpose of the API is to display, add, edit and delete tasks.

## Key criteria:
1. A task is formed of a name (min 3, max 100 characters) and a description (min 10, max 5000 characters).
2. Create an endpoint to view all tasks (no user restrictions).
3. A secured URL is required to edit or delete a task. Provide the appropriate endpoints to do this when a task is created.
4. No user accounts/ authentication is required.
5. Should be a RESTful API with a base of: `/api/tasks`.
6. Uses a SQLite database (this is already setup).
7. Appropriate Unit and/or Feature Tests.
8. Uses Laravel Best Practices.

## Bonus Criteria:
1. All requests should be logged in a log file via middleware.
2. Implement Soft Deleting.

## Submission:
1. Respond to the email you received with a link to the fork of this repository with your solution in. Please include a `.env` file within your repository.
2. The three commands which will be used to run your solution will be:
    1. `composer install`
    2. `php artisan migrate`
    3. `php artisan serve`
