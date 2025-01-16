<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// Courses endpoint
// Get all courses
Route::get('/courses', function () {
    $courses = DB::select('SELECT * FROM courses
                        ORDER BY courses.id ASC');
    foreach ($courses as $course) {
        $course->tests = DB::select('SELECT tests.`name` AS test_name,
                                    tests.`maxvalue`,
                                    courses.`name` AS course_name
                                    FROM tests
                                    INNER JOIN courses ON courses.id = tests.course_id
                                    WHERE course_id = ?
                                    ORDER BY tests.id ASC', [$course->id]);
        if(!$course->tests){
            $course->tests = [];
        }
    }
    return response()->json($courses);
});

// Get a course by ID
Route::get('/courses/{id}', function ($id) {
    $course = DB::select('SELECT name FROM courses WHERE courses.id = ?', [$id]);
    if (empty($course)) {
        return response()->json(['message' => 'course not found'], 404);
    }
    return response()->json($course[0]);
});

// Update a course
Route::put('/courses/{id}', function (\Illuminate\Http\Request $request, $id) {
    $name = $request->input('name');

    $affected = DB::update('UPDATE courses SET courses.name = ? WHERE id = ?', [$name, $id]);

    if ($affected === 0) {
        return response()->json(['message' => 'Course not found or no changes made'], 404);
    }
    return response()->json(['message' => 'Course updated successfully']);
});

// Create a new course
Route::post('/courses', function (\Illuminate\Http\Request $request) {
    $name = $request->input('name');
    DB::insert('INSERT INTO courses (name) 
                VALUES (?)', [$name]);
    return response()->json(['message' => 'course created successfully'], 201);
});

// Delete a course by ID
Route::delete('/courses/{id}', function ($id) {
    $deleted = DB::delete('DELETE FROM courses WHERE id = ?', [$id]);
    if ($deleted === 0) {
        return response()->json(['message' => 'course not found'], 404);
    }
    return response()->json(['message' => 'course deleted successfully']);
});

// Users endpoint
// Get all users
Route::get('/users', function (Request $request) {
    // Start the main query to get users
    $users = DB::select('SELECT 
                            users.id, 
                            firstname, 
                            lastname, 
                            email, 
                            password, 
                            eligible,  
                            roles.name AS role,
                            class_id,
                            classes.name AS class,
                            year AS schoolyear,
                            ROUND((SUM(test_user.value)/SUM(tests.maxvalue)) * 100, 2) AS average
                        FROM users
                        LEFT JOIN roles ON roles.id = users.role_id
                        LEFT JOIN classes ON classes.id = users.class_id
                        INNER JOIN test_user ON test_user.user_id = users.id
                        INNER JOIN tests ON test_user.test_id = tests.id
                        GROUP BY users.id
                        ORDER BY users.firstname ASC');
    foreach ($users as $user) {
        $user->tests = DB::select('SELECT 
                                    tests.name AS test_name, 
                                    test_user.value AS test_value, 
                                    tests.maxvalue AS test_maxvalue 
                                FROM test_user
                                INNER JOIN tests ON test_user.test_id = tests.id
                                WHERE test_user.user_id = ?', [$user->id]);
        if(!$user->tests){
            $user->tests = [];
        }
    }

    foreach ($users as $user) {
        $user->groups = DB::select('SELECT `groups`.id,
                                    `groups`.`name` AS group_name,
                                    courses.`name` AS course_name
                                    FROM group_user
                                    INNER JOIN `groups` On `groups`.id = group_user.group_id
                                    INNER JOIN courses ON `groups`.course_id = courses.id
                                    WHERE group_user.user_id = ?', [$user->id]);
        if(!$user->tests){
            $user->tests = [];
        }
    }

    // Return the nested result as JSON
    return response()->json($users);
});

// Get user by id
Route::get('/users/{id}', function ($id) {
    $user = DB::select('SELECT 
                        firstname, lastname, eligible, roles.name AS role, classes.name AS class, year AS schoolyear
                        FROM users
                        INNER JOIN roles ON roles.id = users.role_id
                        INNER JOIN classes ON classes.id = users.class_id
                        WHERE users.id = ?', [$id]);
    $user = $user[0]; 
    $user->tests = DB::select('SELECT 
                                tests.name AS test_name, 
                                test_user.value AS test_value, 
                                tests.maxvalue AS test_maxvalue 
                                FROM test_user
                                INNER JOIN tests ON test_user.test_id = tests.id
                                WHERE test_user.user_id = ?', [$id]);
    if(!$user->tests){
        $user->tests = [];
    }
    if (empty($user)) {
        return response()->json(['message' => 'user not found'], 404);
    }
    return response()->json($user);
});

// Create a new user
Route::post('/users', function (\Illuminate\Http\Request $request) {
    $firstname = $request->input('firstname');
    $lastname = $request->input('lastname');
    $email = $request->input('email');
    $password = $request->input('password');
    $eligible = $request->input('eligible');
    $role_id = $request->input('role_id');
    $class_id = $request->input('class_id');

    if ($eligible !== 0 && $eligible !== 1) {
        return response()->json(['message' => 'eligibility can only be 0 or 1'], 409);
    }

    DB::insert('INSERT INTO users (firstname, lastname, email, password, eligible, role_id, class_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)', [$firstname, $lastname, $email, $password, $eligible, $role_id, $class_id]);

    return response()->json(['message' => 'user created successfully'], 201);
});

// Update a user
Route::put('/users/{id}', function (\Illuminate\Http\Request $request, $id) {
    $firstname = $request->input('firstname');
    $lastname = $request->input('lastname');
    $email = $request->input('email');
    $password = $request->input('password');
    $eligible = $request->input('eligible');
    $role_id = $request->input('role_id');
    $class_id = $request->input('class_id');
    // this still throws an error when the input is a string
    if ($eligible != 0 && $eligible != 1) {
        return response()->json(['message' => 'eligibility can only be 0 or 1'], 404);
    }

    $affected = DB::update('UPDATE users SET firstname = ?, lastname = ?, email = ?, password = ?, eligible = ?, role_id = ?, class_id = ? WHERE id = ?', [$firstname, $lastname, $email, $password, $eligible, $role_id, $class_id, $id]);

    if ($affected === 0) {
        return response()->json(['message' => 'User not found or no changes made'], 404);
    }
    return response()->json(['message' => 'User updated successfully']);
});

// Delete a user by ID
Route::delete('/users/{id}', function ($id) {
    $deleted = DB::delete('DELETE FROM users WHERE id = ?', [$id]);
    if ($deleted === 0) {
        return response()->json(['message' => 'user not found'], 404);
    }
    return response()->json(['message' => 'user deleted successfully']);
});

// Classes
// Get all classes
Route::get('/classes', function () {
    $class = DB::select('SELECT  * FROM classes');
    return response()->json($class);
});

// Get a class and its students by id
Route::get('/classes/{id}', function ($id) {
    $class = DB::select('SELECT 
                        classes.name AS classname, firstname, lastname, roles.name AS role
                        FROM classes 
                        INNER JOIN (users INNER JOIN roles ON users.role_id = roles.id) ON classes.id = users.class_id 
                        WHERE classes.id = ?', [$id]);
    if (empty($class)) {
        return response()->json(['message' => 'class not found or no students in class'], 404);
    }
    return response()->json($class);
});

//Update a class
Route::put('/classes/{id}', function (\Illuminate\Http\Request $request, $id) {
    $name = $request->input('name');
    $year = $request->input('year');

    $affected = DB::update('UPDATE classes SET name = ?, year = ?  WHERE id = ?', [$name, $year, $id]);

    if ($affected === 0) {
        return response()->json(['message' => 'Class not found or no changes made'], 404);
    }
    return response()->json(['message' => 'Class updated successfully']);
});

// Create a new class
Route::post('/classes', function (\Illuminate\Http\Request $request) {
    $name = $request->input('name');
    $year = $request->input('year');

    DB::insert('INSERT INTO classes (name, year) VALUES (?, ?)', [$name, $year]);

    return response()->json(['message' => 'class created successfully'], 201);
});

// Delete a class by ID
Route::delete('/classes/{id}', function ($id) {
    $deleted = DB::delete('DELETE FROM classes WHERE id = ?', [$id]);
    if ($deleted === 0) {
        return response()->json(['message' => 'class not found'], 404);
    }
    return response()->json(['message' => 'class deleted successfully']);
});

// Groups endpoint
// Get all groups 
// needs changes
Route::get('/groups', function () {
    $groups = DB::select('SELECT 
                        group_user.id, courses.name AS tutoredcourse, groups.name AS groupname, firstname, lastname, users.id AS user_id
                        FROM groups 
                        JOIN (group_user JOIN users ON group_user.user_id = users.id) ON group_user.group_id = groups.id 
                        JOIN courses ON groups.course_id = courses.id');
    return response()->json($groups);
});

//create a group
Route::post('/groups', function (\Illuminate\Http\Request $request) {
    $name = $request->input('name');
    $course_id = $request->input('course_id');

    DB::insert('INSERT INTO groups (name, course_id) VALUES (?, ?)', [$name, $course_id]);

    return response()->json(['message' => 'group created successfully'], 201);
});

// Delete a group by ID
Route::delete('/groups/{id}', function ($id) {
    $deleted = DB::delete('DELETE FROM groups WHERE id = ?', [$id]);
    if ($deleted === 0) {
        return response()->json(['message' => 'group not found'], 404);
    }
    return response()->json(['message' => 'group deleted successfully']);
});

// Roles
// Get all roles
Route::get('/roles', function () {
    $role = DB::select('SELECT * FROM roles');
    return response()->json($role);
});

// Test-users
// Get all users from a test
Route::get('/test-user/{id}', function ($id) {
    $test = DB::select('SELECT 
                        tests.id, tests.name AS testname, value, tests.maxvalue, courses.`name` AS coursename
                        FROM users
                        INNER JOIN test_user ON test_user.user_id = users.id
                        INNER JOIN tests ON test_user.test_id = tests.id
                        INNER JOIN courses ON tests.course_id = courses.id
                        WHERE users.id = ?
                        ;', [$id]);
    if (empty($test)) {
        return response()->json(['message' => 'test not found'], 404);
    }
    return response()->json($test);
});

//tests endpoint
// Get all tests
Route::get('/tests', function () {
    $tests = DB::select('SELECT * FROM tests');
    foreach($tests as $test){
        $test->users = DB::select('SELECT users.id, users.firstname, users.lastname, test_user.value FROM test_user
                                    INNER JOIN users ON test_user.user_id = users.id
                                    WHERE test_user.test_id = ?
                                    ORDER BY users.firstname ASC', [$test->id]);
    }
    return response()->json($tests);
});

Route::get('/tests/{id}', function ($id) {
    $test = DB::select('SELECT * FROM tests WHERE id = ?', [$id]);
    if (empty($test)) {
        return response()->json(['message' => 'test not found'], 404);
    }
    return response()->json($test);
});

// Create a new test
Route::post('/tests', function (\Illuminate\Http\Request $request) {
    $name = $request->input('name');
    $maxvalue = $request->input('maxvalue');
    $course_id = $request->input('course_id');

    DB::insert('INSERT INTO tests (tests.name, tests.maxvalue, tests.course_id) VALUES (?, ?, ?)', [$name, $maxvalue, $course_id]);

    return response()->json(['message' => 'test created successfully'], 201);
});

// Delete a test by ID
Route::delete('/tests/{id}', function ($id) {
    $deleted = DB::delete('DELETE FROM tests WHERE id = ?', [$id]);
    if ($deleted === 0) {
        return response()->json(['message' => 'test not found'], 404);
    }
    return response()->json(['message' => 'test deleted successfully']);
});

// update a test
Route::put('/tests/{id}', function (\Illuminate\Http\Request $request, $id) {
    $name = $request->input('name');
    $maxvalue = $request->input('maxvalue');
    $course_id = $request->input('course_id');

    $affected = DB::update('UPDATE tests SET tests.name = ?, tests.maxvalue = ?, tests.course_id = ?  WHERE id = ?', [$name, $course_id, $maxvalue, $id]);

    if ($affected === 0) {
        return response()->json(['message' => 'Test not found or no changes made'], 404);
    }
    return response()->json(['message' => 'Test updated successfully']);
});

// Course-test

// Get all tests from a course
Route::get('/course-test/{id}', function ($id) {
    $course = DB::select('SELECT courses.name, tests.name, tests.maxvalue 
                            FROM courses 
                            LEFT JOIN tests ON tests.course_id = courses.id 
                            WHERE courses.id = ? ', [$id]);
    if (empty($course)) {
        return response()->json(['message' => 'course not found or no tests for this course'], 404);
    }
    return response()->json($course);
});

Route::get('/averagetotal/{id}', function ($id) {
    $averages = DB::select('SELECT users.id AS user_id,
                        ROUND((SUM(test_user.`value`)/SUM(tests.`maxvalue`)) * 100, 2) AS average
                        FROM users
                        INNER JOIN test_user ON test_user.user_id = users.id
                        INNER JOIN tests ON test_user.test_id = tests.id
                        WHERE users.id = ?
                        group by users.id', [$id]);
    return response()->json($averages);
});
