import { createCrud } from './crud'
export const usersApi = createCrud('users')
// getUsers, createUser, updateUser, deleteUser all go through generic CRUD
// Admin-only endpoints are handled by backend / access control
