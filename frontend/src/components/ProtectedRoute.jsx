export default function ProtectedRoute({
  children,
  currentUser,
  adminOnly = false,
}) {
  if (currentUser === undefined) return <p>Loading...</p> // still fetching user
  if (!currentUser) return <p>Not authenticated</p>
  if (adminOnly && !currentUser.isAdmin) return <p>Not authorized</p>
  return children
}
