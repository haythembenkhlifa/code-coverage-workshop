<?php

use App\Http\Requests\UpdatePostRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('UpdatePostRequest', function () {

    it('passes validation with valid data', function () {

        // Arrange
        $user = User::factory()->create();
        $data = [
            'title' => 'Updated Title',
            'content' => 'Updated content for the post.',
            'user_id' => $user->id,
        ];

        // Action
        $request = new UpdatePostRequest();
        $validator = Validator::make($data, $request->rules());

        // Assert
        expect($validator->passes())->toBeTrue();
    });

    it('passes validation with partial data', function () {

        // Arrange
        $data = [
            'title' => 'Updated Title Only',
        ];

        // Action
        $request = new UpdatePostRequest();
        $validator = Validator::make($data, $request->rules());

        // Assert
        expect($validator->passes())->toBeTrue();
    });

    it('passes validation with empty data', function () {
        // Arrange
        $data = [];

        // Action
        $request = new UpdatePostRequest();
        $validator = Validator::make($data, $request->rules());

        // Assert
        expect($validator->passes())->toBeTrue();
    });

    it('fails validation when title is empty but provided', function () {

        // Arrange
        $data = [
            'title' => '',
        ];

        // Action
        $request = new UpdatePostRequest();
        $validator = Validator::make($data, $request->rules());

        // Assert
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('title'))->toBeTrue();
    });

    it('fails validation when content is empty but provided', function () {

        // Arrange
        $data = [
            'content' => '',
        ];

        // Action
        $request = new UpdatePostRequest();
        $validator = Validator::make($data, $request->rules());

        // Assert
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('content'))->toBeTrue();
    });

    it('fails validation when user_id does not exist', function () {

        // Arrange
        $data = [
            'user_id' => 999, // Non-existent user
        ];

        // Action
        $request = new UpdatePostRequest();
        $validator = Validator::make($data, $request->rules());

        // Assert
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('user_id'))->toBeTrue();
    });

    it('fails validation when title exceeds maximum length', function () {

        // Arrange
        $data = [
            'title' => str_repeat('a', 256), // Exceeds 255 characters
        ];

        // Action
        $request = new UpdatePostRequest();
        $validator = Validator::make($data, $request->rules());

        // Assert
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('title'))->toBeTrue();
    });

    it('has custom error messages', function () {

        // Arrange
        $data = [
            'title' => '',
            'content' => '',
            'user_id' => 999,
        ];

        // Action
        $request = new UpdatePostRequest();
        $validator = Validator::make($data, $request->rules(), $request->messages());

        // Assert
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->first('title'))->toBe('The post title is required when provided.');
        expect($validator->errors()->first('content'))->toBe('The post content is required when provided.');
        expect($validator->errors()->first('user_id'))->toBe('The selected user does not exist.');
    });

    it('validates only provided fields', function () {

        // Arrange
        $user = User::factory()->create();
        $data = [
            'content' => 'Only updating content',
            'user_id' => $user->id,
        ];

        // Action
        $request = new UpdatePostRequest();
        $validator = Validator::make($data, $request->rules());

        // Assert
        expect($validator->passes())->toBeTrue();
    });

    it('authorizes the request', function () {
        // Arrange
        $request = new UpdatePostRequest();

        // Action & Assert
        expect($request->authorize())->toBeTrue();
    });

    it('handles empty values correctly with sometimes validation', function () {

        // Arrange
        $user = User::factory()->create();
        $data = [
            'title' => 'Valid Title',
            'content' => '', // Empty string should fail validation
            'user_id' => $user->id,
        ];

        // Action
        $request = new UpdatePostRequest();
        $validator = Validator::make($data, $request->rules());

        // Assert
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('content'))->toBeTrue();
    });

    it('validates properly when fields are not provided', function () {

        // Arrange
        $data = [
            'title' => 'Only Title Provided',
            // content and user_id not provided - should pass with 'sometimes'
        ];

        // Action
        $request = new UpdatePostRequest();
        $validator = Validator::make($data, $request->rules());

        // Assert
        expect($validator->passes())->toBeTrue();
    });
});
