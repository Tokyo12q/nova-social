# Nova Social - Implementation Roadmap

This roadmap breaks down the development of Nova Social into small, testable phases and incremental features.

In strict adherence to [CLAUDE.md](file:///C:/Users/moham/Documents/nova-social/CLAUDE.md):
- **One Feature at a Time**: Only one feature is implemented per iteration. Stop and wait for user review before starting the next.
- **Architecture Integrity**: Every feature adheres to:
  - Form Request Validation (`app/Http/Requests`)
  - API Resources (`app/Http/Resources`)
  - Authorization via Policies (`app/Policies`)
  - Dedicated Business Logic in Services (`app/Services`)
  - Automated Feature Tests (`tests/Feature`)
- **Manual Verification**: Every feature includes concrete steps to verify functionality manually (via curl / HTTP requests or UI).
- **Package Management**: No new packages are installed without explicit user permission.
- **Language**: All code, tests, docs, and git commits must be in English.

---

## Roadmap Overview

```
Phase 1: Project Setup & Foundation (Backend & Frontend)
Phase 2: Authentication & Account Management
Phase 3: Profiles & Account Privacy
Phase 4: Social Graph (Follows & Blocks)
Phase 5: Posts & Media Uploads
Phase 6: Engagement (Likes, Comments, Saves)
Phase 7: Feeds, Discovery & Hashtags
Phase 8: Ephemeral Stories (24h)
Phase 9: Direct Messaging & Real-Time Chat (Laravel Reverb)
Phase 10: In-App & Real-Time Notifications
Phase 11: Moderation, Reporting & Safety
```

---

## Phase 1: Project Setup & Foundation

### 1.1 Backend Laravel 11 Setup, Database Baseline & Health Check
- **Scope**: Initialize Laravel 11 in `/backend`, configure `.env` with MySQL credentials, configure Sanctum, create migration files for core tables defined in [ERD.md](file:///C:/Users/moham/Documents/nova-social/docs/ERD.md), and provide a health check API endpoint.
- **Components**:
  - `routes/api.php` (`GET /api/v1/health`)
  - Migration files for all entities
  - Feature test: `tests/Feature/HealthCheckTest.php`
- **Acceptance Criteria**:
  - [ ] Running `php artisan migrate` creates all tables, foreign keys, and indexes without errors.
  - [ ] `GET /api/v1/health` returns `200 OK` with JSON `{"status": "ok", "timestamp": "...", "database": "connected"}`.
  - [ ] `HealthCheckTest` passes cleanly (`php artisan test --filter HealthCheckTest`).
- **Manual Verification**:
  ```bash
  curl -X GET http://localhost:8000/api/v1/health
  ```

### 1.2 Frontend Next.js & Tailwind CSS Setup
- **Scope**: Initialize Next.js with Tailwind CSS in `/frontend`, configure Axios/Fetch API client with base URL pointing to the Laravel API, configure environment variables.
- **Acceptance Criteria**:
  - [ ] `npm run dev` in `/frontend` boots without errors on `http://localhost:3000`.
  - [ ] Tailwind CSS utility classes render correctly.
  - [ ] Base API client (`lib/api.ts`) includes default JSON headers and interceptors for Sanctum Bearer tokens.
- **Manual Verification**:
  - Visit `http://localhost:3000` in browser to confirm the landing page loads and styles are applied.

---

## Phase 2: Authentication & Account Management

### 2.1 User Registration
- **Scope**: Register a new user with username, name, email, and password. Automatically initialize a blank profile record for the new user.
- **Components**:
  - `RegisterRequest` (validates unique username, email, strong password confirmation)
  - `AuthService::register()` (creates User and Profile within a DB transaction)
  - `UserResource`
  - `AuthController::register()` (`POST /api/v1/auth/register`)
  - Feature test: `tests/Feature/Auth/RegisterTest.php`
- **Acceptance Criteria**:
  - [ ] Returns `201 Created` with `UserResource` payload and Sanctum API token upon valid inputs.
  - [ ] Returns `422 Unprocessable Content` if username or email already exists.
  - [ ] A corresponding row in `profiles` table is automatically created with `user_id`.
  - [ ] `RegisterTest` passes.
- **Manual Verification**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/auth/register \
    -H "Content-Type: application/json" \
    -d '{"name":"Alice","username":"alice","email":"alice@example.com","password":"Password123!","password_confirmation":"Password123!"}'
  ```

### 2.2 User Login & Sanctum Token Generation
- **Scope**: Authenticate user via username or email and password, returning a Sanctum Bearer token.
- **Components**:
  - `LoginRequest` (handles either username or email login)
  - `AuthService::login()`
  - `AuthController::login()` (`POST /api/v1/auth/login`)
  - Feature test: `tests/Feature/Auth/LoginTest.php`
- **Acceptance Criteria**:
  - [ ] Returns `200 OK` with token and user details for valid credentials.
  - [ ] Returns `401 Unauthorized` for invalid password or nonexistent user.
  - [ ] Returns `403 Forbidden` if user account has `is_active = false`.
  - [ ] `LoginTest` passes.
- **Manual Verification**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/auth/login \
    -H "Content-Type: application/json" \
    -d '{"login":"alice","password":"Password123!"}'
  ```

### 2.3 Authenticated User Profile (`/auth/me`)
- **Scope**: Retrieve the currently authenticated user's profile and settings.
- **Components**:
  - `AuthController::me()` (`GET /api/v1/auth/me`)
  - `UserResource` (includes profile, follower count, following count)
  - Feature test: `tests/Feature/Auth/MeTest.php`
- **Acceptance Criteria**:
  - [ ] Returns `200 OK` with user details when Bearer token is provided.
  - [ ] Returns `401 Unauthorized` when Bearer token is missing or invalid.
  - [ ] `MeTest` passes.
- **Manual Verification**:
  ```bash
  curl -X GET http://localhost:8000/api/v1/auth/me \
    -H "Authorization: Bearer <TOKEN>"
  ```

### 2.4 User Logout & Token Revocation
- **Scope**: Invalidate current active Sanctum token.
- **Components**:
  - `AuthController::logout()` (`POST /api/v1/auth/logout`)
  - `AuthService::logout()`
  - Feature test: `tests/Feature/Auth/LogoutTest.php`
- **Acceptance Criteria**:
  - [ ] Returns `200 OK` with `{"message": "Logged out successfully"}`.
  - [ ] Subsequent requests with the revoked token return `401 Unauthorized`.
  - [ ] `LogoutTest` passes.
- **Manual Verification**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/auth/logout \
    -H "Authorization: Bearer <TOKEN>"
  ```

---

## Phase 3: Profiles & Account Privacy

### 3.1 View User Profile by Username
- **Scope**: Fetch public profile details, bio, avatar, post counts, follower counts, and relationship status relative to the viewer (is_following, is_blocked).
- **Components**:
  - `ProfileController::show()` (`GET /api/v1/profiles/{username}`)
  - `ProfileResource`
  - `ProfileService::getByUsername()`
  - Feature test: `tests/Feature/Profile/ViewProfileTest.php`
- **Acceptance Criteria**:
  - [ ] Returns `200 OK` with user profile details for existing usernames.
  - [ ] Returns `404 Not Found` for non-existing usernames.
  - [ ] If viewer is blocked by target user, returns `404 Not Found` or `403 Forbidden`.
  - [ ] `ViewProfileTest` passes.
- **Manual Verification**:
  ```bash
  curl -X GET http://localhost:8000/api/v1/profiles/alice \
    -H "Authorization: Bearer <TOKEN>"
  ```

### 3.2 Update User Profile & Upload Avatar/Cover
- **Scope**: Update user bio, website, location, avatar image, and cover photo.
- **Components**:
  - `UpdateProfileRequest` (validates bio length, url formats, image mimes: jpg, png, webp, max: 2MB)
  - `ProfilePolicy` (ensures users can only update their own profile)
  - `ProfileService::updateProfile()` (handles file storage to `storage/app/public`)
  - `ProfileController::update()` (`PUT /api/v1/profiles/me`)
  - Feature test: `tests/Feature/Profile/UpdateProfileTest.php`
- **Acceptance Criteria**:
  - [ ] Returns `200 OK` with updated `ProfileResource`.
  - [ ] Uploaded image files are stored securely and public URLs are returned.
  - [ ] Unauthorized modification attempts return `403 Forbidden`.
  - [ ] `UpdateProfileTest` passes.
- **Manual Verification**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/profiles/me \
    -H "Authorization: Bearer <TOKEN>" \
    -F "_method=PUT" \
    -F "bio=Software engineer building cool apps" \
    -F "avatar=@avatar.jpg"
  ```

### 3.3 Privacy & Account Settings
- **Scope**: Toggle account privacy (`is_private` boolean) and update email/password.
- **Components**:
  - `UpdateAccountSettingsRequest`
  - `SettingsService::updateSettings()`
  - `SettingsController::update()` (`PATCH /api/v1/settings/account`)
  - Feature test: `tests/Feature/Profile/AccountSettingsTest.php`
- **Acceptance Criteria**:
  - [ ] Can toggle `is_private` from `false` to `true` and vice versa.
  - [ ] Updating password requires valid `current_password`.
  - [ ] `AccountSettingsTest` passes.
- **Manual Verification**:
  ```bash
  curl -X PATCH http://localhost:8000/api/v1/settings/account \
    -H "Authorization: Bearer <TOKEN>" \
    -H "Content-Type: application/json" \
    -d '{"is_private": true}'
  ```

---

## Phase 4: Social Graph (Follows & Blocks)

### 4.1 Follow & Unfollow User
- **Scope**: Follow a user. If target is public, status is immediately `accepted`. If target is private, status is set to `pending`. Unfollowing removes the record.
- **Components**:
  - `FollowService::toggleFollow()`
  - `FollowController::store()` (`POST /api/v1/users/{id}/follow`)
  - `FollowController::destroy()` (`DELETE /api/v1/users/{id}/follow`)
  - Feature test: `tests/Feature/Follow/FollowUserTest.php`
- **Acceptance Criteria**:
  - [ ] Cannot follow yourself (returns `422 Unprocessable Content`).
  - [ ] Following public account sets `status = 'accepted'` and increments following/follower counts.
  - [ ] Following private account sets `status = 'pending'`.
  - [ ] Unfollowing deletes the follow record and decrements counts.
  - [ ] `FollowUserTest` passes.
- **Manual Verification**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/users/2/follow \
    -H "Authorization: Bearer <TOKEN>"
  ```

### 4.2 Follow Requests Management (For Private Accounts)
- **Scope**: For private accounts, list pending requests, accept or reject them.
- **Components**:
  - `FollowRequestController::index()` (`GET /api/v1/follow-requests`)
  - `FollowRequestController::accept()` (`POST /api/v1/follow-requests/{id}/accept`)
  - `FollowRequestController::reject()` (`POST /api/v1/follow-requests/{id}/reject`)
  - `FollowService::handleRequest()`
  - Feature test: `tests/Feature/Follow/FollowRequestTest.php`
- **Acceptance Criteria**:
  - [ ] Only the user receiving the request can accept/reject (Policy enforced).
  - [ ] Accepting sets `status = 'accepted'` and increments follower counts.
  - [ ] Rejecting deletes the follow row.
  - [ ] `FollowRequestTest` passes.
- **Manual Verification**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/follow-requests/1/accept \
    -H "Authorization: Bearer <TOKEN>"
  ```

### 4.3 Followers & Following Lists
- **Scope**: Paginated list of followers and followings for any profile, with mutual follow indicators.
- **Components**:
  - `FollowController::followers()` (`GET /api/v1/users/{id}/followers`)
  - `FollowController::following()` (`GET /api/v1/users/{id}/following`)
  - `UserSummaryResource`
  - Feature test: `tests/Feature/Follow/FollowListTest.php`
- **Acceptance Criteria**:
  - [ ] If target user is private and viewer does not follow them, returns `403 Forbidden`.
  - [ ] Paginated results with `20` items per page.
  - [ ] `FollowListTest` passes.
- **Manual Verification**:
  ```bash
  curl -X GET http://localhost:8000/api/v1/users/2/followers \
    -H "Authorization: Bearer <TOKEN>"
  ```

### 4.4 User Blocking & Block Enforcement
- **Scope**: Block and unblock another user. Blocking automatically severs any existing follow relationships in both directions and hides profiles/posts/messages.
- **Components**:
  - `BlockController::store()` (`POST /api/v1/users/{id}/block`)
  - `BlockController::destroy()` (`DELETE /api/v1/users/{id}/block`)
  - `BlockController::index()` (`GET /api/v1/users/blocked`)
  - `BlockService::blockUser()`, `BlockService::unblockUser()`
  - Feature test: `tests/Feature/Block/BlockUserTest.php`
- **Acceptance Criteria**:
  - [ ] Cannot block yourself (`422`).
  - [ ] Blocking deletes any existing `follows` records between both users.
  - [ ] Blocked user cannot view the blocker's profile or content (`404` or `403`).
  - [ ] `BlockUserTest` passes.
- **Manual Verification**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/users/2/block \
    -H "Authorization: Bearer <TOKEN>"
  ```

---

## Phase 5: Posts & Media Uploads

### 5.1 Create Post with Media
- **Scope**: Upload a post with caption and 1 to 10 media files (photos/videos). Extract and link any `#hashtags`.
- **Components**:
  - `StorePostRequest` (validates caption text length <= 2200, media array min: 1, max: 10, file types jpg, png, mp4)
  - `PostService::createPost()` (stores media files, persists `post_media`, extracts hashtags)
  - `PostResource`
  - `PostController::store()` (`POST /api/v1/posts`)
  - Feature test: `tests/Feature/Post/CreatePostTest.php`
- **Acceptance Criteria**:
  - [ ] Returns `201 Created` with `PostResource` including eager-loaded `post_media` and `user`.
  - [ ] Creates records in `posts`, `post_media`, `hashtags`, and `post_hashtags`.
  - [ ] Returns `422` if no media is provided.
  - [ ] `CreatePostTest` passes.
- **Manual Verification**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/posts \
    -H "Authorization: Bearer <TOKEN>" \
    -F "caption=Enjoying my weekend! #sunny #travel" \
    -F "media[]=@photo1.jpg"
  ```

### 5.2 View Single Post
- **Scope**: Retrieve a post by ID with author details, media array, like count, comment count, and viewer's `is_liked`/`is_saved` states.
- **Components**:
  - `PostController::show()` (`GET /api/v1/posts/{id}`)
  - `PostPolicy::view()` (enforces private account privacy and blocking checks)
  - `PostResource`
  - Feature test: `tests/Feature/Post/ViewPostTest.php`
- **Acceptance Criteria**:
  - [ ] Returns `200 OK` with complete post details for authorized viewers.
  - [ ] Returns `403 Forbidden` if author account is private and viewer is not an accepted follower.
  - [ ] Returns `404 Not Found` if author has blocked viewer or post does not exist.
  - [ ] `ViewPostTest` passes.
- **Manual Verification**:
  ```bash
  curl -X GET http://localhost:8000/api/v1/posts/1 \
    -H "Authorization: Bearer <TOKEN>"
  ```

### 5.3 Edit and Soft-Delete Post
- **Scope**: Author can update caption and comments toggle, or delete their post.
- **Components**:
  - `UpdatePostRequest`
  - `PostPolicy::update()`, `PostPolicy::delete()`
  - `PostService::updatePost()`, `PostService::deletePost()`
  - `PostController::update()` (`PUT /api/v1/posts/{id}`)
  - `PostController::destroy()` (`DELETE /api/v1/posts/{id}`)
  - Feature test: `tests/Feature/Post/ManagePostTest.php`
- **Acceptance Criteria**:
  - [ ] Only post author can edit or delete (returns `403` for non-owners).
  - [ ] Deleting post sets `deleted_at` (soft delete) and decrements hashtag post counters.
  - [ ] `ManagePostTest` passes.
- **Manual Verification**:
  ```bash
  curl -X PUT http://localhost:8000/api/v1/posts/1 \
    -H "Authorization: Bearer <TOKEN>" \
    -H "Content-Type: application/json" \
    -d '{"caption":"Updated caption #travel"}'
  ```

### 5.4 User Profile Post Grid
- **Scope**: Paginated feed of posts created by a specific user.
- **Components**:
  - `PostController::userPosts()` (`GET /api/v1/users/{username}/posts`)
  - `PostPolicy` privacy checks
  - Feature test: `tests/Feature/Post/UserPostsTest.php`
- **Acceptance Criteria**:
  - [ ] Returns paginated array of user posts ordered by `created_at DESC`.
  - [ ] Respects private profile access rules.
  - [ ] `UserPostsTest` passes.
- **Manual Verification**:
  ```bash
  curl -X GET http://localhost:8000/api/v1/users/alice/posts \
    -H "Authorization: Bearer <TOKEN>"
  ```

---

## Phase 6: Engagement (Likes, Comments, Saves)

### 6.1 Like & Unlike Posts
- **Scope**: Like and unlike a post using polymorphic `likes` table and maintain denormalized `posts.likes_count`.
- **Components**:
  - `LikeController::togglePostLike()` (`POST /api/v1/posts/{id}/like`)
  - `LikeService::toggleLike()`
  - Feature test: `tests/Feature/Engagement/PostLikeTest.php`
- **Acceptance Criteria**:
  - [ ] First request creates like row, increments `likes_count`, returns `{"liked": true, "likes_count": 1}`.
  - [ ] Second request removes like row, decrements `likes_count`, returns `{"liked": false, "likes_count": 0}`.
  - [ ] Atomic database transaction guarantees counter accuracy under concurrent requests.
  - [ ] `PostLikeTest` passes.
- **Manual Verification**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/posts/1/like \
    -H "Authorization: Bearer <TOKEN>"
  ```

### 6.2 Comments & Nested Replies
- **Scope**: Add top-level comments and replies to comments on posts. List comments with pagination.
- **Components**:
  - `StoreCommentRequest` (validates content max 1000, optional parent_id)
  - `CommentService::createComment()`, `CommentService::deleteComment()`
  - `CommentResource`
  - `CommentController::store()` (`POST /api/v1/posts/{id}/comments`)
  - `CommentController::index()` (`GET /api/v1/posts/{id}/comments`)
  - `CommentController::destroy()` (`DELETE /api/v1/comments/{id}`)
  - Feature test: `tests/Feature/Engagement/CommentTest.php`
- **Acceptance Criteria**:
  - [ ] Returns `403` if post has `comments_disabled = true`.
  - [ ] Increments `posts.comments_count`.
  - [ ] Nested comments reference valid parent comment on the same post.
  - [ ] Author of post or author of comment can delete comment.
  - [ ] `CommentTest` passes.
- **Manual Verification**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/posts/1/comments \
    -H "Authorization: Bearer <TOKEN>" \
    -H "Content-Type: application/json" \
    -d '{"content":"Great shot!"}'
  ```

### 6.3 Like & Unlike Comments
- **Scope**: Like and unlike comments using the polymorphic `likes` table.
- **Components**:
  - `LikeController::toggleCommentLike()` (`POST /api/v1/comments/{id}/like`)
  - Feature test: `tests/Feature/Engagement/CommentLikeTest.php`
- **Acceptance Criteria**:
  - [ ] Toggles like state and increments/decrements `comments.likes_count`.
  - [ ] `CommentLikeTest` passes.
- **Manual Verification**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/comments/1/like \
    -H "Authorization: Bearer <TOKEN>"
  ```

### 6.4 Save / Bookmark Posts
- **Scope**: Save post to user bookmarks and retrieve saved posts collection.
- **Components**:
  - `SaveController::toggle()` (`POST /api/v1/posts/{id}/save`)
  - `SaveController::index()` (`GET /api/v1/saved-posts`)
  - `SaveService::toggleSave()`
  - Feature test: `tests/Feature/Engagement/SavePostTest.php`
- **Acceptance Criteria**:
  - [ ] Toggles saved state and updates `posts.saves_count`.
  - [ ] `GET /api/v1/saved-posts` returns only posts saved by the authenticated user.
  - [ ] `SavePostTest` passes.
- **Manual Verification**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/posts/1/save \
    -H "Authorization: Bearer <TOKEN>"
  ```

---

## Phase 7: Feeds, Discovery & Hashtags

### 7.1 Chronological Home Feed
- **Scope**: Paginated feed composed of posts published by users the viewer actively follows, plus the viewer's own posts. Excludes blocked users.
- **Components**:
  - `FeedController::home()` (`GET /api/v1/feed`)
  - `FeedService::getHomeFeed()` (cursor pagination via `created_at`)
  - `PostResource`
  - Feature test: `tests/Feature/Feed/HomeFeedTest.php`
- **Acceptance Criteria**:
  - [ ] Only displays posts from accepted followees and own posts.
  - [ ] Filters out any posts from blocked or blocking users.
  - [ ] Cursor-based pagination functions without duplicate entries across pages.
  - [ ] `HomeFeedTest` passes.
- **Manual Verification**:
  ```bash
  curl -X GET "http://localhost:8000/api/v1/feed?limit=15" \
    -H "Authorization: Bearer <TOKEN>"
  ```

### 7.2 Hashtags Discovery & Tag Feeds
- **Scope**: List posts associated with a hashtag and retrieve top trending hashtags.
- **Components**:
  - `HashtagController::show()` (`GET /api/v1/hashtags/{name}`)
  - `HashtagController::trending()` (`GET /api/v1/hashtags/trending`)
  - `HashtagService`
  - Feature test: `tests/Feature/Feed/HashtagFeedTest.php`
- **Acceptance Criteria**:
  - [ ] `GET /api/v1/hashtags/travel` returns posts tagged with `#travel` from public accounts.
  - [ ] `GET /api/v1/hashtags/trending` returns hashtags ordered by `posts_count DESC`.
  - [ ] `HashtagFeedTest` passes.
- **Manual Verification**:
  ```bash
  curl -X GET http://localhost:8000/api/v1/hashtags/travel \
    -H "Authorization: Bearer <TOKEN>"
  ```

### 7.3 Explore Feed & Global Search
- **Scope**: Algorithmic/popular explore feed of public posts and global search endpoint for usernames, full names, and hashtags.
- **Components**:
  - `FeedController::explore()` (`GET /api/v1/explore`)
  - `SearchController::search()` (`GET /api/v1/search?q={query}`)
  - `SearchService`
  - Feature test: `tests/Feature/Feed/ExploreAndSearchTest.php`
- **Acceptance Criteria**:
  - [ ] Explore feed only contains posts from public accounts.
  - [ ] Search query matches usernames, names, and hashtags with partial matching.
  - [ ] Blocked users are omitted from search results.
  - [ ] `ExploreAndSearchTest` passes.
- **Manual Verification**:
  ```bash
  curl -X GET "http://localhost:8000/api/v1/search?q=alice" \
    -H "Authorization: Bearer <TOKEN>"
  ```

---

## Phase 8: Ephemeral Stories (24h)

### 8.1 Create Ephemeral Story
- **Scope**: Upload a photo or video story that automatically expires 24 hours after creation (`expires_at = created_at + 24 hours`).
- **Components**:
  - `StoreStoryRequest` (validates media type image/video, caption max 500)
  - `StoryService::createStory()`
  - `StoryResource`
  - `StoryController::store()` (`POST /api/v1/stories`)
  - Feature test: `tests/Feature/Story/CreateStoryTest.php`
- **Acceptance Criteria**:
  - [ ] Returns `201 Created` with `expires_at` set to exactly 24 hours in the future.
  - [ ] `CreateStoryTest` passes.
- **Manual Verification**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/stories \
    -H "Authorization: Bearer <TOKEN>" \
    -F "media=@story.jpg" \
    -F "caption=Morning vibes!"
  ```

### 8.2 Stories Tray / Active Stories Feed
- **Scope**: Get grouped active stories from users the viewer follows, with viewed indicators.
- **Components**:
  - `StoryController::feed()` (`GET /api/v1/stories/feed`)
  - `StoryService::getStoriesFeed()`
  - Feature test: `tests/Feature/Story/StoryFeedTest.php`
- **Acceptance Criteria**:
  - [ ] Excludes any story where `expires_at <= NOW()`.
  - [ ] Groups stories by author with an indicator `all_viewed: boolean`.
  - [ ] `StoryFeedTest` passes.
- **Manual Verification**:
  ```bash
  curl -X GET http://localhost:8000/api/v1/stories/feed \
    -H "Authorization: Bearer <TOKEN>"
  ```

### 8.3 Story Viewing & Viewer List
- **Scope**: Record a view on a story and allow the story author to see the list of viewers.
- **Components**:
  - `StoryController::view()` (`POST /api/v1/stories/{id}/view`)
  - `StoryController::viewers()` (`GET /api/v1/stories/{id}/viewers`)
  - `StoryPolicy::viewers()` (only author can see viewers list)
  - `StoryService::recordView()`
  - Feature test: `tests/Feature/Story/StoryViewTest.php`
- **Acceptance Criteria**:
  - [ ] Viewing story creates record in `story_views` and increments `stories.views_count`.
  - [ ] Viewing multiple times by the same viewer does not duplicate count.
  - [ ] Non-author cannot access viewers list (`403 Forbidden`).
  - [ ] `StoryViewTest` passes.
- **Manual Verification**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/stories/1/view \
    -H "Authorization: Bearer <TOKEN>"
  ```

### 8.4 Delete Story
- **Scope**: Story author can delete an active story prematurely.
- **Components**:
  - `StoryController::destroy()` (`DELETE /api/v1/stories/{id}`)
  - `StoryPolicy::delete()`
  - Feature test: `tests/Feature/Story/DeleteStoryTest.php`
- **Acceptance Criteria**:
  - [ ] Author can delete story (`204 No Content`).
  - [ ] Non-author receives `403 Forbidden`.
  - [ ] `DeleteStoryTest` passes.
- **Manual Verification**:
  ```bash
  curl -X DELETE http://localhost:8000/api/v1/stories/1 \
    -H "Authorization: Bearer <TOKEN>"
  ```

---

## Phase 9: Direct Messaging & Real-Time Chat (Laravel Reverb)

### 9.1 Conversations List & Initiation
- **Scope**: Start or open a 1-on-1 direct chat or group chat. Retrieve inbox conversations sorted by `last_message_at DESC`.
- **Components**:
  - `StartConversationRequest`
  - `ConversationService::getOrCreateDirect()`, `ConversationService::createGroup()`
  - `ConversationResource`
  - `ConversationController::index()` (`GET /api/v1/conversations`)
  - `ConversationController::store()` (`POST /api/v1/conversations`)
  - Feature test: `tests/Feature/Chat/ConversationTest.php`
- **Acceptance Criteria**:
  - [ ] Cannot start direct conversation with a user who blocked you or is blocked (`403`).
  - [ ] Calling store for an existing 1-on-1 pair returns the existing conversation instead of duplicating.
  - [ ] Inbox lists conversations with latest message snippet and unread count.
  - [ ] `ConversationTest` passes.
- **Manual Verification**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/conversations \
    -H "Authorization: Bearer <TOKEN>" \
    -H "Content-Type: application/json" \
    -d '{"recipient_id": 2}'
  ```

### 9.2 Send Message & Message History
- **Scope**: Send text and media messages within a conversation and fetch message history with cursor pagination.
- **Components**:
  - `SendMessageRequest` (validates body text or media file)
  - `MessageService::sendMessage()`
  - `MessageResource`
  - `MessageController::index()` (`GET /api/v1/conversations/{id}/messages`)
  - `MessageController::store()` (`POST /api/v1/conversations/{id}/messages`)
  - Feature test: `tests/Feature/Chat/MessageTest.php`
- **Acceptance Criteria**:
  - [ ] Only conversation participants can view and send messages (Policy protected).
  - [ ] Updates `conversations.last_message_at` timestamp.
  - [ ] Messages are returned in reverse chronological order with cursor pagination.
  - [ ] `MessageTest` passes.
- **Manual Verification**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/conversations/1/messages \
    -H "Authorization: Bearer <TOKEN>" \
    -H "Content-Type: application/json" \
    -d '{"body":"Hello there!"}'
  ```

### 9.3 Real-Time Message Broadcasting via Laravel Reverb
- **Scope**: Broadcast `MessageSent` event over private WebSocket channel (`private-chat.{conversationId}`) via Laravel Reverb when a message is sent.
- **Components**:
  - Event: `App\Events\MessageSent` implements `ShouldBroadcast`
  - Channel authorization in `routes/channels.php`
  - Feature test: `tests/Feature/Chat/BroadcastMessageTest.php`
- **Acceptance Criteria**:
  - [ ] `MessageSent` is queued and broadcast to channel `private-chat.{conversationId}`.
  - [ ] Unauthorized users cannot subscribe to channel `private-chat.{conversationId}`.
  - [ ] `BroadcastMessageTest` passes.
- **Manual Verification**:
  - Connect WebSocket client (or Laravel Echo) to Reverb on `ws://localhost:8080`, authenticate channel, and verify JSON payload upon message dispatch.

---

## Phase 10: In-App & Real-Time Notifications

### 10.1 In-App Notification Generation
- **Scope**: Trigger notifications when users are followed, posts are liked, comments are added, or user is mentioned in a caption.
- **Components**:
  - Notification classes: `NewFollowerNotification`, `PostLikedNotification`, `CommentAddedNotification`
  - Notification triggers integrated in respective Service classes
  - `NotificationController::index()` (`GET /api/v1/notifications`)
  - Feature test: `tests/Feature/Notification/NotificationTest.php`
- **Acceptance Criteria**:
  - [ ] Acting on one's own post/comment does NOT generate a notification to oneself.
  - [ ] Notifications table records polymorphic target and JSON details.
  - [ ] `NotificationTest` passes.
- **Manual Verification**:
  ```bash
  curl -X GET http://localhost:8000/api/v1/notifications \
    -H "Authorization: Bearer <TOKEN>"
  ```

### 10.2 Mark Notifications as Read
- **Scope**: Mark single notification or all notifications as read.
- **Components**:
  - `NotificationController::markAsRead()` (`PATCH /api/v1/notifications/{id}/read`)
  - `NotificationController::markAllAsRead()` (`POST /api/v1/notifications/read-all`)
  - Feature test: `tests/Feature/Notification/MarkReadNotificationTest.php`
- **Acceptance Criteria**:
  - [ ] Sets `read_at` timestamp.
  - [ ] Only owner can mark their notifications as read.
  - [ ] `MarkReadNotificationTest` passes.
- **Manual Verification**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/notifications/read-all \
    -H "Authorization: Bearer <TOKEN>"
  ```

### 10.3 Real-Time Notification Broadcasting via Reverb
- **Scope**: Broadcast real-time notification alerts to user's private channel (`private-user.{id}`) via Reverb.
- **Components**:
  - Broadcast channel in `routes/channels.php` (`App.Models.User.{id}`)
  - Notification implements `ShouldBroadcast`
  - Feature test: `tests/Feature/Notification/BroadcastNotificationTest.php`
- **Acceptance Criteria**:
  - [ ] Live notification event is broadcast on user private channel upon action.
  - [ ] `BroadcastNotificationTest` passes.
- **Manual Verification**:
  - Verify Echo client receives notification event in real time.

---

## Phase 11: Content Moderation & Safety

### 11.1 File User & Content Reports
- **Scope**: Any user can report posts, comments, stories, or other user accounts for safety violations (spam, harassment, hate speech, etc.).
- **Components**:
  - `StoreReportRequest` (validates reportable type, id, reason enum)
  - `ReportService::fileReport()`
  - `ReportController::store()` (`POST /api/v1/reports`)
  - Feature test: `tests/Feature/Moderation/FileReportTest.php`
- **Acceptance Criteria**:
  - [ ] Creates row in `reports` with `status = 'pending'`.
  - [ ] Prevents duplicate spam reports on the same target by the same reporter.
  - [ ] `FileReportTest` passes.
- **Manual Verification**:
  ```bash
  curl -X POST http://localhost:8000/api/v1/reports \
    -H "Authorization: Bearer <TOKEN>" \
    -H "Content-Type: application/json" \
    -d '{"reportable_type":"post","reportable_id":1,"reason":"spam","details":"Automated bot spam"}'
  ```

### 11.2 Moderator Triage & Content Removal
- **Scope**: Moderator/Admin review queue to resolve reports, dismiss reports, remove flagged content, or suspend accounts.
- **Components**:
  - `ModerationController::index()` (`GET /api/v1/admin/reports`)
  - `ModerationController::update()` (`PATCH /api/v1/admin/reports/{id}`)
  - `ModerationPolicy` (restricts to users with `role IN ('moderator', 'admin')`)
  - `ModerationService::resolveReport()`
  - Feature test: `tests/Feature/Moderation/ModeratorTriageTest.php`
- **Acceptance Criteria**:
  - [ ] Non-moderators receive `403 Forbidden`.
  - [ ] Resolving report with action `remove_content` soft-deletes the target post/comment.
  - [ ] Resolving report with action `suspend_user` sets target user's `is_active = false`.
  - [ ] `ModeratorTriageTest` passes.
- **Manual Verification**:
  ```bash
  curl -X PATCH http://localhost:8000/api/v1/admin/reports/1 \
    -H "Authorization: Bearer <ADMIN_TOKEN>" \
    -H "Content-Type: application/json" \
    -d '{"status":"resolved","action":"remove_content","notes":"Confirmed spam link"}'
  ```

---

## Workflow Reminders for Implementation

1. **Strictly One Task at a Time**:
   Always execute features in sequence (e.g. 1.1 -> wait for review -> 1.2 -> wait for review -> 2.1 ...).
2. **Never install unapproved packages**:
   Ask for explicit permission before running any `composer require` or `npm install`.
3. **Always Run Tests & Manual Verification**:
   Ensure automated tests run cleanly and provide manual verification steps upon completing each feature.
