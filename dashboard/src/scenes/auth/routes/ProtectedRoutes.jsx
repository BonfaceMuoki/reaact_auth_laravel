import { useLocation, Navigate, Outlet } from "react-router-dom"
import { useSelector } from "react-redux"
import { selectCurrentPermissions, selectCurrentRoles, selectCurrentToken,selectCurrentUser } from "../authSlice"

const RequireAuth = () => {
    const token = useSelector(selectCurrentToken);
    const location = useLocation();
    const user = useSelector(selectCurrentUser);
    const roles = useSelector(selectCurrentRoles);  
    return (
        token
            ? <Outlet/>
            : <Navigate to="/login" state={{ from: location }} replace />
    )
}
export default RequireAuth