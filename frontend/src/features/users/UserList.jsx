import { Link } from 'react-router-dom'

export default function UsersList() {
  return (
    <div className="max-w-4xl mx-auto mt-10 p-6 bg-white shadow rounded-xl">
      <h1 className="text-2xl font-bold mb-4">Users</h1>
      <p>No users yet.</p>
      <Link
        to="/users/create"
        className="inline-block mt-4 bg-blue-600 text-white p-2 rounded hover:bg-blue-700"
      >
        Add New User
      </Link>
    </div>
  )
}
