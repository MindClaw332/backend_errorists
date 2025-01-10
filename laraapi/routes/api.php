<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Courses

    // Get all courses
    Route::get('/courses', function () {
        $course = DB::select('SELECT name FROM courses');
        return response()->json($course);
    });

    // Get a course by ID
    Route::get('/courses/{id}', function ($id) {
        $course = DB::select('SELECT name FROM courses WHERE courses.id = ?', [$id]);
        if (empty($course)) {
            return response()->json(['message' => 'course not found'], 404);
        }
        return response()->json($course[0]);
    });

    // Create a new course
    Route::post('/courses', function (\Illuminate\Http\Request $request) {
        $name = $request->input('name');

        DB::insert('INSERT INTO courses (name) VALUES (?)', [$name]);

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

// Course-test

    // Get all tests from a course
    Route::get('/course-test', function () {
        $course = DB::select('SELECT 
                                courses.name, tests.name, tests.maxvalue 
                                FROM courses 
                                JOIN tests ON tests.course_id = courses.id 
                                WHERE courses.id = ?');
        return response()->json($course);
    });

// Tests

    // Get all tests
    Route::get('/tests', function () {
        $test = DB::select('SELECT 
                            courses.name, tests.name, value, tests.maxvalue, firstname, lastname 
                            FROM tests 
                            LEFT JOIN (test_user JOIN users ON test_user.user_id = users.id) ON test_user.test_id = tests.id 
                            JOIN courses ON tests.course_id = courses.id');
        return response()->json($test);
    });

    // Get a test by ID
    Route::get('/tests/{id}', function ($id) {
        $test = DB::select('SELECT 
                            courses.name, tests.name, value, tests.maxvalue, firstname, lastname 
                            FROM tests 
                            LEFT JOIN (test_user JOIN users ON test_user.user_id = users.id) ON test_user.test_id = tests.id 
                            JOIN courses ON tests.course_id = courses.id 
                            WHERE tests.id = ?', [$id]);
        if (empty($test)) {
            return response()->json(['message' => 'test not found'], 404);
        }
        return response()->json($test[0]);
    });

    // Create a new test
    Route::post('/tests', function (\Illuminate\Http\Request $request) {
        $name = $request->input('name');
        $maxvalue = $request->input('maxvalue');
        $course_id = $request->input('course_id');

        DB::insert('INSERT INTO tests (name, maxvalue, course_id) VALUES (?, ?, ?)', [$name, $maxvalue, $course_id]);

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

// Test-users

    // Get all users from a test
    Route::get('/test-users', function () {
        $test = DB::select('SELECT 
                            firstname, lastname, tests.name AS testname, value, tests.maxvalue
                            FROM users
                            LEFT JOIN test_user ON test_user.user_id = users.id
                            LEFT JOIN tests ON test_user.test_id = tests.id
                            WHERE tests.id = ?');
        return response()->json($test);
    });

// Roles

    // Get all roles
    Route::get('/roles', function () {
        $role = DB::select('SELECT name FROM roles');
        return response()->json($course);
    });

// Groups 

    // Get all groups
    Route::get('/groups', function () {
        $group = DB::select('SELECT 
                                courses.name, groups.name, firstname, lastname 
                                FROM groups 
                                JOIN (group_user JOIN users ON group_user.user_id = users.id) ON group_user.group_id = groups.id 
                                JOIN courses ON groups.course_id = courses.id');
        return response()->json($group);
    });

    // Get a group by ID
    Route::get('/groups/{id}', function ($id) {
        $group = DB::select('SELECT 
                                courses.name, groups.name, firstname, lastname 
                                FROM groups 
                                JOIN (group_user JOIN users ON group_user.user_id = users.id) ON group_user.group_id = groups.id 
                                JOIN courses ON groups.course_id = courses.id 
                                WHERE groups.id = ?', [$id]);
        if (empty($group)) {
            return response()->json(['message' => 'group not found'], 404);
        }
        return response()->json($group[0]);
    });

    // Create a new group
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

// Classes

        // Get all classes
        Route::get('/classes', function () {
            $class = DB::select('SELECT 
                                    classes.name, firstname, lastname, roles.name 
                                    FROM classes 
                                    JOIN (users JOIN roles ON users.role_id = roles.id) ON classes.id = users.class_id');
            return response()->json($class);
        });
    
        // Get a class by ID
        Route::get('/classes/{id}', function ($id) {
            $class = DB::select('SELECT 
                                    classes.name, firstname, lastname, roles.name 
                                    FROM classes 
                                    JOIN (users JOIN roles ON users.role_id = roles.id) ON classes.id = users.class_id 
                                    WHERE classes.id = ?', [$id]);
            if (empty($class)) {
                return response()->json(['message' => 'class not found'], 404);
            }
            return response()->json($class[0]);
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

// Users

        // Get all users
        Route::get('/users', function () {
            $user = DB::select('SELECT 
                                    firstname, lastname, email, password, eligible, roles.name, classes.name, year
                                    FROM users
                                    LEFT JOIN roles ON roles.id = users.role_id
                                    LEFT JOIN classes ON classes.id = users.class_id');
            return response()->json($user);
        });
    
        // Get a user by ID
        Route::get('/users/{id}', function ($id) {
            $user = DB::select('SELECT 
                                    firstname, lastname, email, password, eligible, roles.name, classes.name, year
                                    FROM users
                                    LEFT JOIN roles ON roles.id = users.role_id
                                    LEFT JOIN classes ON classes.id = users.class_id
                                    WHERE users.id = ?', [$id]);
            if (empty($user)) {
                return response()->json(['message' => 'user not found'], 404);
            }
            return response()->json($user[0]);
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
    
            DB::insert('INSERT INTO users (firstname, lastname, email, password, eligible, role_id, class_id) VALUES (?, ?, ?, ?, ?, ?, ?)', [$firstname, $lastname, $email, $password, $eligible, $role_id, $course_id]);
    
            return response()->json(['message' => 'user created successfully'], 201);
        });
    
        // Delete a user by ID
        Route::delete('/users/{id}', function ($id) {
            $deleted = DB::delete('DELETE FROM users WHERE id = ?', [$id]);
            if ($deleted === 0) {
                return response()->json(['message' => 'user not found'], 404);
            }
            return response()->json(['message' => 'user deleted successfully']);
        });