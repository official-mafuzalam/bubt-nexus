<x-admin-layout>
    @section('title', 'API Documentation')
    <x-slot name="main">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="mb-8">
                            <h1 class="text-3xl font-bold mb-2">API Documentation</h1>
                            <p class="text-gray-600 dark:text-gray-400">
                                This documentation describes the available API endpoints for the application.
                            </p>
                        </div>

                        <!-- Authentication Endpoints -->
                        <div class="mb-10">
                            <h2 class="text-2xl font-semibold mb-4 border-b border-gray-300 dark:border-gray-700 pb-2">
                                Authentication Endpoints
                            </h2>

                            <!-- Register Endpoint -->
                            <div class="mb-6">
                                <div class="flex items-center mb-2">
                                    <span
                                        class="bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">POST</span>
                                    <code class="text-lg font-mono">/api/register</code>
                                </div>
                                <p class="mb-3 text-gray-700 dark:text-gray-300">Register a new user account.</p>
                                <div class="bg-gray-100 dark:bg-gray-900 p-4 rounded-md mb-3">
                                    <h4 class="font-medium mb-2">Example Request:</h4>
                                    <pre class="text-sm"><code class="language-json">{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password"
}</code></pre>
                                </div>
                                <div class="bg-gray-100 dark:bg-gray-900 p-4 rounded-md">
                                    <h4 class="font-medium mb-2">Example Response:</h4>
                                    <pre class="text-sm"><code class="language-json">{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": null,
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z"
  },
  "token": "1|a1b2c3d4e5f6g7h8i9j0"
}</code></pre>
                                </div>
                            </div>

                            <!-- Login Endpoint -->
                            <div class="mb-6">
                                <div class="flex items-center mb-2">
                                    <span
                                        class="bg-green-100 text-green-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">POST</span>
                                    <code class="text-lg font-mono">/api/login</code>
                                </div>
                                <p class="mb-3 text-gray-700 dark:text-gray-300">Authenticate a user and retrieve an
                                    access token.</p>
                                <div class="bg-gray-100 dark:bg-gray-900 p-4 rounded-md mb-3">
                                    <h4 class="font-medium mb-2">Example Request:</h4>
                                    <pre class="text-sm"><code class="language-json">{
  "email": "john@example.com",
  "password": "password"
}</code></pre>
                                </div>
                                <div class="bg-gray-100 dark:bg-gray-900 p-4 rounded-md">
                                    <h4 class="font-medium mb-2">Example Response:</h4>
                                    <pre class="text-sm"><code class="language-json">{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": null,
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z"
  },
  "token": "1|a1b2c3d4e5f6g7h8i9j0"
}</code></pre>
                                </div>
                            </div>

                            <!-- Logout Endpoint -->
                            <div class="mb-6">
                                <div class="flex items-center mb-2">
                                    <span
                                        class="bg-red-100 text-red-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">POST</span>
                                    <code class="text-lg font-mono">/api/logout</code>
                                    <span
                                        class="ml-2 bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">Auth
                                        Required</span>
                                </div>
                                <p class="mb-3 text-gray-700 dark:text-gray-300">Revoke the current access token
                                    (logout).</p>
                                <div class="bg-gray-100 dark:bg-gray-900 p-4 rounded-md">
                                    <h4 class="font-medium mb-2">Headers:</h4>
                                    <pre class="text-sm"><code>Authorization: Bearer {token}
Accept: application/json</code></pre>
                                </div>
                            </div>

                            <!-- User Endpoint -->
                            <div class="mb-6">
                                <div class="flex items-center mb-2">
                                    <span
                                        class="bg-blue-100 text-blue-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">GET</span>
                                    <code class="text-lg font-mono">/api/user</code>
                                    <span
                                        class="ml-2 bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">Auth
                                        Required</span>
                                </div>
                                <p class="mb-3 text-gray-700 dark:text-gray-300">Get the authenticated user's details.
                                </p>
                                <div class="bg-gray-100 dark:bg-gray-900 p-4 rounded-md">
                                    <h4 class="font-medium mb-2">Headers:</h4>
                                    <pre class="text-sm"><code>Authorization: Bearer {token}
Accept: application/json</code></pre>
                                </div>
                            </div>
                        </div>

                        <!-- Public Endpoints -->
                        <div>
                            <h2 class="text-2xl font-semibold mb-4 border-b border-gray-300 dark:border-gray-700 pb-2">
                                Public Endpoints
                            </h2>

                            <!-- Notices Endpoint -->
                            <div class="mb-6">
                                <div class="flex items-center mb-2">
                                    <span
                                        class="bg-blue-100 text-blue-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">GET</span>
                                    <code class="text-lg font-mono">/api/notices</code>
                                    <span
                                        class="ml-2 bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-yellow-900 dark:text-yellow-300">Rate
                                        Limited</span>
                                </div>
                                <p class="mb-3 text-gray-700 dark:text-gray-300">Retrieve application notices. This
                                    endpoint is rate limited to 5 requests per minute.</p>
                                <div class="bg-gray-100 dark:bg-gray-900 p-4 rounded-md">
                                    <h4 class="font-medium mb-2">Example Response:</h4>
                                    <pre class="text-sm"><code class="language-json">[
  {
    "id": 1,
    "title": "System Maintenance",
    "content": "The system will be down for maintenance on...",
    "created_at": "2023-01-01T00:00:00.000000Z",
    "updated_at": "2023-01-01T00:00:00.000000Z"
  },
  {
    "id": 2,
    "title": "New Feature Release",
    "content": "We've released a new feature that allows...",
    "created_at": "2023-01-02T00:00:00.000000Z",
    "updated_at": "2023-01-02T00:00:00.000000Z"
  }
]</code></pre>
                                </div>
                            </div>
                        </div>

                        <!-- Authentication Note -->
                        <div class="mt-8 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-md">
                            <h3 class="font-semibold text-blue-800 dark:text-blue-300 mb-2">Authentication Note</h3>
                            <p class="text-blue-700 dark:text-blue-400">
                                Endpoints marked with "Auth Required" need a Bearer token in the Authorization header.
                                Obtain a token by registering or logging in.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>
</x-admin-layout>
