<?php

use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\Hash;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);


it('can be created with factory', function () {
    // Action
    $user = User::factory()->create();

    // Assert
    expect($user)->toBeInstanceOf(User::class);
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
    ]);
});

it('can be created with attributes', function () {

    // Arrange
    $userData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
    ];

    // Action
    $user = User::create($userData);

    // Assert
    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('John Doe')
        ->and($user->email)->toBe('john@example.com');

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
});

it('has correct fillable attributes', function () {

    // Arrange
    $user = new User();

    // Action
    $fillable = $user->getFillable();

    // Assert
    expect($fillable)->toBe(['name', 'email', 'password']);
});

it('has correct hidden attributes', function () {

    // Arrange
    $user = User::factory()->create();

    // Action
    $hidden = $user->getHidden();

    // Assert
    expect($hidden)->toBe(['password', 'remember_token']);
});

it('hides password in array representation', function () {

    // Arrange
    $user = User::factory()->create();

    // Action
    $userArray = $user->toArray();

    // Assert
    expect($userArray)->not->toHaveKey('password')
        ->and($userArray)->not->toHaveKey('remember_token');
});

it('hides password in json representation', function () {

    // Arrange
    $user = User::factory()->create();

    // Action
    $userJson = $user->toJson();
    $userArray = json_decode($userJson, true);

    // Assert
    expect($userArray)->not->toHaveKey('password')
        ->and($userArray)->not->toHaveKey('remember_token');
});

it('hashes password when creating user', function () {

    // Arrange
    $plainPassword = 'password123';

    // Action
    $user = User::factory()->create([
        'password' => $plainPassword,
    ]);

    // Assert
    expect(Hash::check($plainPassword, $user->password))->toBeTrue()
        ->and($user->password)->not->toBe($plainPassword);
});

it('casts email_verified_at to datetime', function () {
    // Action
    $user = User::factory()->create();

    // Assert
    expect($user->email_verified_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('allows email_verified_at to be null', function () {
    // Action
    $user = User::factory()->unverified()->create();

    // Assert
    expect($user->email_verified_at)->toBeNull();
});

it('requires name field', function () {
    // Action & Assert
    User::create([
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);
})->throws(\Illuminate\Database\QueryException::class);

it('requires email field', function () {
    // Action & Assert
    User::create([
        'name' => 'Test User',
        'password' => 'password123',
    ]);
})->throws(\Illuminate\Database\QueryException::class);

it('enforces unique email constraint', function () {

    // Arrange
    $email = 'test@example.com';
    User::factory()->create(['email' => $email]);

    // Action & Assert
    User::factory()->create(['email' => $email]);
})->throws(\Illuminate\Database\QueryException::class);

it('has timestamps', function () {
    // Action
    $user = User::factory()->create();

    // Assert
    expect($user->created_at)->not->toBeNull()
        ->and($user->updated_at)->not->toBeNull()
        ->and($user->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($user->updated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('can be updated', function () {

    // Arrange
    $user = User::factory()->create();
    $newName = 'Updated Name';

    // Action
    $user->update(['name' => $newName]);

    // Assert
    expect($user->fresh()->name)->toBe($newName);
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => $newName,
    ]);
});

it('can be deleted', function () {

    // Arrange
    $user = User::factory()->create();
    $userId = $user->id;

    // Action
    $user->delete();

    // Assert
    $this->assertDatabaseMissing('users', ['id' => $userId]);
});

it('factory creates unique emails', function () {
    // Action
    $users = User::factory()->count(5)->create();
    $emails = $users->pluck('email')->toArray();
    $uniqueEmails = array_unique($emails);

    // Assert
    expect($uniqueEmails)->toHaveCount(5);
});

it('can set remember token', function () {

    // Arrange
    $user = User::factory()->create();
    $token = 'test_remember_token';

    // Action
    $user->setRememberToken($token);
    $user->save();

    // Assert
    expect($user->fresh()->remember_token)->toBe($token);
});

it('has posts relationship', function () {

    // Arrange
    $user = User::factory()->create();

    // Action
    $relationship = $user->posts();

    // Assert
    expect($relationship)->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
    expect($relationship->getRelated())->toBeInstanceOf(Post::class);
});

it('can have multiple posts', function () {

    // Arrange
    $user = User::factory()->create();
    $posts = Post::factory()->count(3)->create(['user_id' => $user->id]);

    // Action
    $userPosts = $user->posts;

    // Assert
    expect($userPosts)->toHaveCount(3);
    expect($userPosts->first())->toBeInstanceOf(Post::class);
    expect($userPosts->pluck('id')->toArray())->toBe($posts->pluck('id')->toArray());
});

it('can create posts through relationship', function () {

    // Arrange
    $user = User::factory()->create();

    // Action
    $post = $user->posts()->create([
        'title' => 'Test Post via Relationship',
        'content' => 'Content created through user relationship.',
    ]);

    // Assert
    expect($post)->toBeInstanceOf(Post::class);
    expect($post->user_id)->toBe($user->id);
    $this->assertDatabaseHas('posts', [
        'title' => 'Test Post via Relationship',
        'user_id' => $user->id,
    ]);
});

it('has correct casts array', function () {

    // Arrange
    $user = new User();

    // Action
    $casts = $user->getCasts();

    // Assert
    expect($casts)->toHaveKey('email_verified_at')
        ->and($casts)->toHaveKey('password')
        ->and($casts['email_verified_at'])->toBe('datetime')
        ->and($casts['password'])->toBe('hashed');
});

it('uses correct traits', function () {

    // Arrange
    $user = new User();

    // Action
    $traits = class_uses($user);

    // Assert
    expect($traits)->toHaveKey(\Illuminate\Database\Eloquent\Factories\HasFactory::class)
        ->and($traits)->toHaveKey(\Illuminate\Notifications\Notifiable::class);
});

it('extends authenticatable user', function () {
    // Arrange
    $user = new User();

    // Action & Assert
    expect($user)->toBeInstanceOf(\Illuminate\Foundation\Auth\User::class);
});

it('can get remember token', function () {

    // Arrange
    $user = User::factory()->create();
    $token = 'test_token_123';
    $user->setRememberToken($token);
    $user->save();

    // Action
    $retrievedToken = $user->getRememberToken();

    // Assert
    expect($retrievedToken)->toBe($token);
});

it('has correct table name', function () {
    // Arrange
    $user = new User();

    // Action
    $tableName = $user->getTable();

    // Assert
    expect($tableName)->toBe('users');
});

it('has correct primary key', function () {
    // Arrange
    $user = new User();

    // Action
    $primaryKey = $user->getKeyName();

    // Assert
    expect($primaryKey)->toBe('id');
});

it('can check if email is verified', function () {

    // Arrange
    $verifiedUser = User::factory()->create();
    $unverifiedUser = User::factory()->unverified()->create();

    // Action & Assert
    expect($verifiedUser->hasVerifiedEmail())->toBeTrue();
    expect($unverifiedUser->hasVerifiedEmail())->toBeFalse();
});

it('can get auth identifier', function () {

    // Arrange
    $user = User::factory()->create();

    // Action
    $authId = $user->getAuthIdentifier();

    // Assert
    expect($authId)->toBe($user->id);
});

it('can get auth password', function () {

    // Arrange
    $user = User::factory()->create();

    // Action
    $authPassword = $user->getAuthPassword();

    // Assert
    expect($authPassword)->toBe($user->password);
});

it('can get remember token name', function () {
    // Arrange
    $user = new User();

    // Action
    $tokenName = $user->getRememberTokenName();

    // Assert
    expect($tokenName)->toBe('remember_token');
});

it('can send notifications', function () {

    // Arrange
    $user = User::factory()->create();

    // Action & Assert
    expect(method_exists($user, 'notify'))->toBeTrue();
    expect(method_exists($user, 'notifyNow'))->toBeTrue();
});
