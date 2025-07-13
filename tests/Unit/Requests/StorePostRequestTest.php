<?php

use App\Http\Requests\StorePostRequest;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('StorePostRequest', function () {

    it('passes validation with valid data', function () {

        // Arrange
        $user = User::factory()->create();
        $data = [
            'title' => 'Valid Title',
            'content' => 'Valid content for the post.',
            'user_id' => $user->id,
        ];

        // Action
        $request = new StorePostRequest();
        $validator = Validator::make($data, $request->rules());

        // Assert
        expect($validator->passes())->toBeTrue();
    });

    it('fails validation when title is missing', function () {

        // Arrange
        $user = User::factory()->create();
        $data = [
            'content' => 'Valid content for the post.',
            'user_id' => $user->id,
        ];

        // Action
        $request = new StorePostRequest();
        $validator = Validator::make($data, $request->rules());

        // Assert
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('title'))->toBeTrue();
    });

    it('fails validation when content is missing', function () {

        // Arrange
        $user = User::factory()->create();
        $data = [
            'title' => 'Valid Title',
            'user_id' => $user->id,
        ];

        // Action
        $request = new StorePostRequest();
        $validator = Validator::make($data, $request->rules());

        // Assert
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('content'))->toBeTrue();
    });

    it('fails validation when user_id is missing', function () {

        // Arrange
        $data = [
            'title' => 'Valid Title',
            'content' => 'Valid content for the post.',
        ];

        // Action
        $request = new StorePostRequest();
        $validator = Validator::make($data, $request->rules());

        // Assert
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('user_id'))->toBeTrue();
    });

    it('fails validation when user_id does not exist', function () {

        // Arrange
        $data = [
            'title' => 'Valid Title',
            'content' => 'Valid content for the post.',
            'user_id' => 999, // Non-existent user
        ];

        // Action
        $request = new StorePostRequest();
        $validator = Validator::make($data, $request->rules());

        // Assert
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('user_id'))->toBeTrue();
    });

    it('fails validation when title exceeds maximum length', function () {

        // Arrange
        $user = User::factory()->create();
        $data = [
            'title' => str_repeat('a', 256), // Exceeds 255 characters
            'content' => 'Valid content for the post.',
            'user_id' => $user->id,
        ];

        // Action
        $request = new StorePostRequest();
        $validator = Validator::make($data, $request->rules());

        // Assert
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('title'))->toBeTrue();
    });

    it('has custom error messages', function () {

        // Arrange
        $data = [];

        // Action
        $request = new StorePostRequest();
        $validator = Validator::make($data, $request->rules(), $request->messages());

        // Assert
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->first('title'))->toBe('The post title is required.');
        expect($validator->errors()->first('content'))->toBe('The post content is required.');
        expect($validator->errors()->first('user_id'))->toBe('The user ID is required.');
    });

    it('authorizes the request', function () {
        // Arrange
        $request = new StorePostRequest();

        // Action & Assert
        expect($request->authorize())->toBeTrue();
    });
});
