<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Courses endpoint
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
