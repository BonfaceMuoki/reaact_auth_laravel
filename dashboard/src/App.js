import { CssBaseline, ThemeProvider } from "@mui/material";
import { createTheme } from "@mui/material/styles";
import { useMemo } from "react";
import { useSelector } from "react-redux";
import { BrowserRouter, Navigate, Routes, Route } from "react-router-dom";
import { themeSettings } from "theme";
import Layout from "./scenes/layouts/Layout";
import AuthLayout from "./scenes/layouts/AuthLayout";
import ValuerDashboard from "./scenes/Dashboard/ValuerDashboard";
import Unauthorized from "scenes/auth/Unauthorized";

import Login from "./scenes/auth/Login";
import Signup from "./scenes/auth/Signup";
import  SignupAccesor from "./scenes/auth/SignupAccesor";
import ProtectedRoutes from "scenes/auth/routes/ProtectedRoutes";
import AuthorizeRoute from "scenes/auth/routes/AuthorizeRoute";
import AdminDashboard from "scenes/Dashboard/AdminDashboard";
import AccesorDashboard from "scenes/Dashboard/AccesorDashboard";
import AdminValuationReports from "scenes/reports/AdminValuationReports";
import RolesTable from "scenes/settings/RolesTable";
import PermissionsTable from "scenes/settings/PermissionsTable";
import RolePermissionTable from "scenes/settings/RolePermissionTable";
import ValuationFirms from "scenes/organizations/ValuationFirms";
import UsersList from "scenes/users/UsersList";
import ReportConsumers from "scenes/organizations/ReportConsumers";
import AcceptInviteByLogin from "scenes/auth/AcceptInviteByLogin";
import AcceptInviteSignup from "scenes/auth/AcceptInviteBySignup";
import AcceptAccesorInviteSignup from "scenes/auth/AcceptAccesorInviteSignup";
import UploaderValuationReports from "scenes/reports/UploaderValuationReports";
import AccesorValuationReports from "scenes/reports/AccesorValuationReports";
import AccesorUserList from "scenes/users/AccesorUserList";
import AccesorValuationFirms from "scenes/organizations/AccesorValuationFirms";
import UploaderReportConsumers from "scenes/organizations/UploaderReportConsumers";
import UploaderUsersList from "scenes/users/UploaderUserList";
import ProfilePage from "scenes/users/ProfilePage";
import AccesorProfilePage from "scenes/users/AccesorProfilePage";
import AcceptValuationUserInviteSignup from "scenes/auth/AcceptValuationUserInviteBySignup";
import AcceptAccesorUserInviteSignup from "scenes/auth/AcceptAccesorUserInviteSignup";
import ForgotPassword from "scenes/auth/ForgotPassword";
import ResetPassword from "scenes/auth/ResetPassword";
import ValuationFirmRequests from "scenes/organizations/ValuationFirmRequests";
import AccesorRegistrationRequests from "scenes/organizations/AccesorRegistrationRequests";
import ReportSubmit from "scenes/reports/ReportSubmit";

function App() {
  const mode = useSelector((state) => state.auth.mode);
  const theme = useMemo(() => createTheme(themeSettings(mode)), [mode]);
  return (
    <div className="App">
      <BrowserRouter>
        <ThemeProvider theme={theme}>
          <CssBaseline />
          <Routes>
            <Route element={<AuthLayout />}>
              <Route path="/login" element={<Login />} />
              <Route path="/request-valuer-access" element={<Signup />} />
              <Route path="/request-accesor-access" element={<SignupAccesor />} />
              <Route path="/forgot-password" element={<ForgotPassword />} />
              <Route path="/reset-user-password" element={<ResetPassword />} />
              
              <Route path="/payments" element={<Signup />} />
              <Route
                path="/complete-invite-by-registering"
                element={<AcceptInviteSignup />}
              />
              <Route
                path="/complete-accesor-invite-by-registering"
                element={<AcceptAccesorInviteSignup />}
              />
              <Route
                path="/complete-invite-by-login"
                element={<AcceptInviteByLogin />}
              />
              {/* register valuation firm user */}
              <Route
                path="/complete-valuer-user-registration-register"
                element={<AcceptValuationUserInviteSignup />}
              />
              <Route
                path="/complete-valuer-user-registration-login"
                element={<AcceptInviteByLogin />}
              />
              {/* close register accesor firm user */}
                           {/* register valuation firm user */}
                           <Route
                path="/complete-accesor-user-registration-register"
                element={<AcceptAccesorUserInviteSignup />}
              />
       
              {/* close register accesor firm user */}
            </Route>
            <Route element={<ProtectedRoutes />}>
              <Route element={<Layout />}>
                <Route path="/unauthorized" element={<Unauthorized />} />
                <Route
                  element={
                    <AuthorizeRoute checkpermission="view valuer dashbaord" />
                  }
                >
                  <Route
                    path="/"
                    element={<Navigate to="/dashboard" replace />}
                  />
                </Route>

                <Route
                  element={
                    <AuthorizeRoute checkpermission="view super admin dashbaord" />
                  }
                >
                  <Route path="/admin-dashboard" element={<AdminDashboard />} />
                </Route>

                <Route
                  element={
                    <AuthorizeRoute checkpermission="view super admin dashbaord" />
                  }
                >
                  <Route path="/valuation-firm-requests" element={<ValuationFirmRequests />} />
                </Route>

                <Route
                  element={
                    <AuthorizeRoute checkpermission="view super admin dashbaord" />
                  }
                >
                  <Route path="/clients-requests" element={<AccesorRegistrationRequests />} />
                </Route>

                
                <Route
                  element={
                    <AuthorizeRoute checkpermission="view all reports" />
                  }
                >
                  <Route
                    path="/all-valuation-reports"
                    element={<AdminValuationReports />}
                  />
                </Route>

                <Route
                  element={<AuthorizeRoute checkpermission="view accesors" />}
                >
                  <Route path="/roles" element={<RolesTable />} />
                </Route>
                <Route
                  element={<AuthorizeRoute checkpermission="view accesors" />}
                >
                  <Route path="/permissions" element={<PermissionsTable />} />
                </Route>
                <Route
                  element={<AuthorizeRoute checkpermission="view accesors" />}
                >
                  <Route
                    path="/role-permissions"
                    element={<RolePermissionTable />}
                  />
                </Route>

                <Route
                  element={<AuthorizeRoute checkpermission="view accesors" />}
                >
                  <Route path="/valuation-firms" element={<ValuationFirms />} />
                </Route>

                <Route
                  element={<AuthorizeRoute checkpermission="view accesors" />}
                >
                  <Route
                    path="/report-consumers"
                    element={<ReportConsumers />}
                  />
                </Route>
                <Route
                  element={<AuthorizeRoute checkpermission="view accesors" />}
                >
                  <Route path="/all-system-users" element={<UsersList />} />
                </Route>
                <Route
                  element={<AuthorizeRoute checkpermission="view accesors" />}
                >
                  <Route path="/valuation-firm-users" element={<UsersList />} />
                </Route>
                <Route
                  element={<AuthorizeRoute checkpermission="view accesors" />}
                >
                  <Route path="/accesors-firm-users" element={<UsersList />} />
                </Route>

                {/* valuers routes */}
                <Route
                  element={
                    <AuthorizeRoute checkpermission="view valuation firm dashboard" />
                  }
                >
                  <Route
                    path="/valuer-dashboard"
                    element={<ValuerDashboard />}
                  />
                </Route>
                <Route
                  element={
                    <AuthorizeRoute checkpermission="view valuation firm reports only" />
                  }
                >
                  <Route
                    path="/valuation-firm/my-reports"
                    element={<UploaderValuationReports />}
                  />
                </Route>
                <Route
                  element={
                    <AuthorizeRoute checkpermission="view valuation firm reports only" />
                  }
                >
                  <Route
                    path="/valuation-firm/submit-report"
                    element={<ReportSubmit />}
                  />
                </Route>
                <Route
                  element={
                    <AuthorizeRoute checkpermission="view valuation firm reports only" />
                  }
                >
                  <Route
                    path="/valuation-firm/my-users"
                    element={<UploaderUsersList/>}
                  />
                </Route>
                <Route
                  element={
                    <AuthorizeRoute checkpermission="view valuation firm reports only" />
                  }
                >
                  <Route
                    path="/valuation-firm/my-clients"
                    element={<UploaderReportConsumers/>}
                  />
                </Route>
                <Route
                  element={
                    <AuthorizeRoute checkpermission="view valuation firm reports only" />
                  }
                >
                  <Route
                    path="/valuation-firm/my-clients"
                    element={<UploaderValuationReports />}
                  />
                </Route>
                <Route
                    path="/profile"
                    element={<ProfilePage />}
                  />
                {/* close valuers routes */}
                {/* accessors routes */}
                
                <Route
                  element={
                    <AuthorizeRoute checkpermission="view accesors dashboard" />
                  }
                >
                  <Route
                    path="/accessor-dashboard"
                    element={<AccesorDashboard />}
                  />
                </Route>
                <Route
                  element={
                    <AuthorizeRoute checkpermission="view accesors dashboard" />
                  }
                >
                  <Route
                    path="/accessor/my-reports"
                    element={<AccesorValuationReports />}
                  />
                </Route>
                <Route
                  element={
                    <AuthorizeRoute checkpermission="view accesors users only" />
                  }
                >
                  <Route
                    path="/accessor/my-users"
                    element={<AccesorUserList />}
                  />
                </Route>
                <Route
                  element={
                    <AuthorizeRoute checkpermission="view accesors uploaders only" />
                  }
                >
                  <Route
                    path="/accessor/my-valuers"
                    element={<AccesorValuationFirms />}
                  />
                </Route> 
                {/* <Route
                  element={
                    <AuthorizeRoute checkpermission="view accesors uploaders only" />
                  }
                > */}
                  <Route
                    path="/accesor/profile"
                    element={<AccesorProfilePage />}
                  />
                {/* </Route>  */}
                {/* /* close accesors rutes */}
              </Route>
            </Route>
          </Routes>
        </ThemeProvider>
      </BrowserRouter>
    </div>
  );
}

export default App;
