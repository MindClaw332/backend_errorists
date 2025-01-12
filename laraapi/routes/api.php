<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// Courses endpoint
// Get all courses
Route::get('/courses', function () {
    $course = DB::select('SELECT * FROM courses
                        ORDER BY name ASC');
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
Route::get('/users', function () {
    $user = DB::select('SELECT 
                        users.id, firstname, lastname, email, password, eligible, roles.name AS role, classes.name AS class, year AS schoolyear
                        FROM users
                        LEFT JOIN roles ON roles.id = users.role_id
                        LEFT JOIN classes ON classes.id = users.class_id
                        ORDER BY users.firstname ASC');
    return response()->json($user);
});

// Get user by id
Route::get('/users/{id}', function ($id) {
    $user = DB::select('SELECT 
                        firstname, lastname, email, password, eligible, roles.name AS role, classes.name AS class, year AS schoolyear
                        FROM users
                        INNER JOIN roles ON roles.id = users.role_id
                        INNER JOIN classes ON classes.id = users.class_id
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

    if ($eligible !== 0 && $eligible !== 1) {
        return response()->json(['message' => 'eligibility can only be 0 or 1'], 409);
    }

    DB::insert('INSERT INTO users (firstname, lastname, email, password, eligible, role_id, class_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)', [$firstname, $lastname, $email, $password, $eligible, $role_id, $class_id]);

    return response()->json(['message' => 'user created successfully'], 201);
});

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

//