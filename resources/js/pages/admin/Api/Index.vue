<script setup lang="ts">
import AdminLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>


<template>
    <Head title="API Documentation" />

    <AdminLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                >
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <!-- Header -->
                        <div class="mb-8">
                            <h1 class="mb-2 text-3xl font-bold">
                                API Documentation
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400">
                                This documentation describes the available API
                                endpoints for the application.
                            </p>
                        </div>

                        <!-- Authentication Endpoints -->
                        <div class="mb-10">
                            <h2
                                class="mb-4 border-b border-gray-300 pb-2 text-2xl font-semibold dark:border-gray-700"
                            >
                                Authentication Endpoints
                            </h2>

                            <!-- Register Endpoint -->
                            <div class="mb-6">
                                <div class="mb-2 flex items-center">
                                    <span
                                        class="mr-2 rounded bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-300"
                                        >POST</span
                                    >
                                    <code class="font-mono text-lg"
                                        >/api/register</code
                                    >
                                </div>
                                <p
                                    class="mb-3 text-gray-700 dark:text-gray-300"
                                >
                                    Register a new user account.
                                </p>

                                <div
                                    class="mb-3 rounded-md bg-gray-100 p-4 dark:bg-gray-900"
                                >
                                    <h4 class="mb-2 font-medium">
                                        Example Request:
                                    </h4>
                                    <pre
                                        class="text-sm"
                                    ><code class="language-json">{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password",
  "password_confirmation": "password"
}</code></pre>
                                </div>

                                <div
                                    class="rounded-md bg-gray-100 p-4 dark:bg-gray-900"
                                >
                                    <h4 class="mb-2 font-medium">
                                        Example Response:
                                    </h4>
                                    <pre
                                        class="text-sm"
                                    ><code class="language-json">{
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
                                <div class="mb-2 flex items-center">
                                    <span
                                        class="mr-2 rounded bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-300"
                                        >POST</span
                                    >
                                    <code class="font-mono text-lg"
                                        >/api/login</code
                                    >
                                </div>
                                <p
                                    class="mb-3 text-gray-700 dark:text-gray-300"
                                >
                                    Authenticate a user and retrieve an access
                                    token.
                                </p>

                                <div
                                    class="mb-3 rounded-md bg-gray-100 p-4 dark:bg-gray-900"
                                >
                                    <h4 class="mb-2 font-medium">
                                        Example Request:
                                    </h4>
                                    <pre
                                        class="text-sm"
                                    ><code class="language-json">{
  "email": "john@example.com",
  "password": "password"
}</code></pre>
                                </div>

                                <div
                                    class="rounded-md bg-gray-100 p-4 dark:bg-gray-900"
                                >
                                    <h4 class="mb-2 font-medium">
                                        Example Response:
                                    </h4>
                                    <pre
                                        class="text-sm"
                                    ><code class="language-json">{
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
                                <div class="mb-2 flex items-center">
                                    <span
                                        class="mr-2 rounded bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-300"
                                        >POST</span
                                    >
                                    <code class="font-mono text-lg"
                                        >/api/logout</code
                                    >
                                    <span
                                        class="ml-2 rounded bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                                        >Auth Required</span
                                    >
                                </div>
                                <p
                                    class="mb-3 text-gray-700 dark:text-gray-300"
                                >
                                    Revoke the current access token (logout).
                                </p>
                                <div
                                    class="rounded-md bg-gray-100 p-4 dark:bg-gray-900"
                                >
                                    <h4 class="mb-2 font-medium">Headers:</h4>
                                    <pre
                                        class="text-sm"
                                    ><code>Authorization: Bearer {token}
Accept: application/json</code></pre>
                                </div>
                            </div>

                            <!-- User Endpoint -->
                            <div class="mb-6">
                                <div class="mb-2 flex items-center">
                                    <span
                                        class="mr-2 rounded bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                                        >GET</span
                                    >
                                    <code class="font-mono text-lg"
                                        >/api/user</code
                                    >
                                    <span
                                        class="ml-2 rounded bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                                        >Auth Required</span
                                    >
                                </div>
                                <p
                                    class="mb-3 text-gray-700 dark:text-gray-300"
                                >
                                    Get the authenticated user's details.
                                </p>
                                <div
                                    class="rounded-md bg-gray-100 p-4 dark:bg-gray-900"
                                >
                                    <h4 class="mb-2 font-medium">Headers:</h4>
                                    <pre
                                        class="text-sm"
                                    ><code>Authorization: Bearer {token}
Accept: application/json</code></pre>
                                </div>
                            </div>
                        </div>

                        <!-- Public Endpoints -->
                        <div>
                            <h2
                                class="mb-4 border-b border-gray-300 pb-2 text-2xl font-semibold dark:border-gray-700"
                            >
                                Public Endpoints
                            </h2>

                            <!-- Notices Endpoint -->
                            <div class="mb-6">
                                <div class="mb-2 flex items-center">
                                    <span
                                        class="mr-2 rounded bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300"
                                        >GET</span
                                    >
                                    <code class="font-mono text-lg"
                                        >/api/notices</code
                                    >
                                    <span
                                        class="ml-2 rounded bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300"
                                        >Rate Limited</span
                                    >
                                </div>
                                <p
                                    class="mb-3 text-gray-700 dark:text-gray-300"
                                >
                                    Retrieve application notices. This endpoint
                                    is rate limited to 5 requests per minute.
                                </p>

                                <div
                                    class="rounded-md bg-gray-100 p-4 dark:bg-gray-900"
                                >
                                    <h4 class="mb-2 font-medium">
                                        Example Response:
                                    </h4>
                                    <pre
                                        class="text-sm"
                                    ><code class="language-json">[
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
                        <div
                            class="mt-8 rounded-md bg-blue-50 p-4 dark:bg-blue-900/20"
                        >
                            <h3
                                class="mb-2 font-semibold text-blue-800 dark:text-blue-300"
                            >
                                Authentication Note
                            </h3>
                            <p class="text-blue-700 dark:text-blue-400">
                                Endpoints marked with "Auth Required" need a
                                Bearer token in the Authorization header. Obtain
                                a token by registering or logging in.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
