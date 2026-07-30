import React, { useState } from 'react';
import './App.css';

export default function App() {
  // Authentication State
  const [user, setUser] = useState(null); // null when logged out, or { role: 'member' | 'staff', name: string }
  const [loginRole, setLoginRole] = useState('member'); // 'member' | 'staff'
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');

  // Portal Navigation
  const [currentView, setCurrentView] = useState('dashboard');
  
  // Modals & Notifications State
  const [isSurveyModalOpen, setIsSurveyModalOpen] = useState(false);
  const [isEditSurveyModalOpen, setIsEditSurveyModalOpen] = useState(false);
  const [isAddMemberModalOpen, setIsAddMemberModalOpen] = useState(false);
  const [toastMessage, setToastMessage] = useState(null);

  // Form States (Member Portal)
  const [fullName, setFullName] = useState('Juan Dela Cruz');
  const [email, setEmail] = useState('example@gmail.com');

  // Trigger Toast Notification
  const triggerToast = (title, message) => {
    setToastMessage({ title, message });
    setTimeout(() => {
      setToastMessage(null);
    }, 4000);
  };

  // Login Submit Handler
  const handleLogin = (e) => {
    e.preventDefault();
    if (loginRole === 'staff') {
      setUser({ role: 'staff', name: 'Staff Admin' });
    } else {
      setUser({ role: 'member', name: fullName, accountNo: 'Cooperative Account Number' });
    }
    setCurrentView('dashboard');
    triggerToast('Welcome Back', `Logged in successfully as ${loginRole === 'staff' ? 'Staff Administrator' : 'Member'}.`);
  };

  // Logout Handler
  const handleLogout = () => {
    setUser(null);
    setUsername('');
    setPassword('');
  };

  /* ==========================================================================
     RENDER LOGIN VIEW
     ========================================================================== */
  if (!user) {
    return (
      <div className="login-wrapper">
        <div className="login-card">
          <div className="login-brand">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            <span>Coop Portal</span>
          </div>
          <div className="login-subtitle">Sign in to access your dashboard</div>

          {/* Role Toggle Selector */}
          <div className="role-toggle">
            <button 
              type="button" 
              className={`role-btn ${loginRole === 'member' ? 'active' : ''}`}
              onClick={() => setLoginRole('member')}
            >
              Member Login
            </button>
            <button 
              type="button" 
              className={`role-btn ${loginRole === 'staff' ? 'active' : ''}`}
              onClick={() => setLoginRole('staff')}
            >
              Staff Admin
            </button>
          </div>

          <form onSubmit={handleLogin}>
            <div className="form-group">
              <label>{loginRole === 'member' ? 'Account Number / Email' : 'Username'}</label>
              <input 
                type="text" 
                className="form-control" 
                placeholder={loginRole === 'member' ? 'e.g. ACC-10023' : 'e.g. admin_staff'}
                value={username}
                onChange={(e) => setUsername(e.target.value)}
                required
              />
            </div>

            <div className="form-group">
              <label>Password</label>
              <input 
                type="password" 
                className="form-control" 
                placeholder="••••••••"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
              />
            </div>

            <button type="submit" className="btn-green-submit btn-full">
              Login to {loginRole === 'staff' ? 'Staff Portal' : 'Member Portal'}
            </button>
          </form>
        </div>
      </div>
    );
  }

  /* ==========================================================================
     RENDER MAIN DASHBOARD (STAFF & MEMBER)
     ========================================================================== */
  return (
    <div className="app-container">
      {/* ---------------- SIDEBAR ---------------- */}
      <aside className="sidebar">
        <div>
          <div className="sidebar-header">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            <span>{user.role === 'staff' ? 'Staff Admin Portal' : 'Member Portal'}</span>
          </div>

          <nav className="sidebar-nav">
            <div 
              className={`nav-item ${currentView === 'dashboard' ? 'active' : ''}`}
              onClick={() => setCurrentView('dashboard')}
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
              <span>Dashboard</span>
            </div>

            <div 
              className={`nav-item ${currentView === 'surveys' ? 'active' : ''}`}
              onClick={() => setCurrentView('surveys')}
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
              <span>{user.role === 'staff' ? 'Survey Management' : 'Available Surveys'}</span>
            </div>

            {user.role === 'staff' ? (
              <>
                <div 
                  className={`nav-item ${currentView === 'analytics' ? 'active' : ''}`}
                  onClick={() => setCurrentView('analytics')}
                >
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
                  <span>Results & Analytics</span>
                </div>

                <div 
                  className={`nav-item ${currentView === 'reports' ? 'active' : ''}`}
                  onClick={() => setCurrentView('reports')}
                >
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                  <span>Reports</span>
                </div>

                <div 
                  className={`nav-item ${currentView === 'members' ? 'active' : ''}`}
                  onClick={() => setCurrentView('members')}
                >
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                  <span>Member Management</span>
                </div>
              </>
            ) : (
              <>
                <div 
                  className={`nav-item ${currentView === 'profile' ? 'active' : ''}`}
                  onClick={() => setCurrentView('profile')}
                >
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                  <span>My Profile</span>
                </div>

                <div 
                  className={`nav-item ${currentView === 'password' ? 'active' : ''}`}
                  onClick={() => setCurrentView('password')}
                >
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.778-7.778zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
                  <span>Change Password</span>
                </div>
              </>
            )}
          </nav>
        </div>

        <div className="sidebar-footer">
          <button className="logout-btn" onClick={handleLogout}>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            <span>Logout</span>
          </button>
        </div>
      </aside>

      {/* ---------------- MAIN WORKSPACE ---------------- */}
      <div className="main-wrapper">
        <header className="top-header">
          <div className="page-title">
            {user.role === 'staff' ? (
              <>
                {currentView === 'dashboard' && 'Staff Dashboard'}
                {currentView === 'surveys' && 'Survey Management'}
                {currentView === 'analytics' && 'Results & Analytics'}
                {currentView === 'reports' && 'Reports'}
                {currentView === 'members' && 'Member Management'}
              </>
            ) : (
              <>
                {currentView === 'dashboard' && 'Dashboard'}
                {currentView === 'surveys' && 'Available Surveys'}
                {currentView === 'profile' && 'My Profile'}
                {currentView === 'password' && 'Change Password'}
              </>
            )}
          </div>

          <div className="user-profile-badge">
            <div className="notification-bell">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#666" strokeWidth="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
              <span className="bell-badge">3</span>
            </div>
            <span>{user.role === 'staff' ? 'Staff Admin' : `${fullName} (Cooperative Account Number)`}</span>
          </div>
        </header>

        {/* CONTENT BODY */}
        <div className="content-body">
          {/* ==================== STAFF VIEWS ==================== */}
          {user.role === 'staff' && (
            <>
              {/* STAFF DASHBOARD */}
              {currentView === 'dashboard' && (
                <div className="metrics-row">
                  <div className="metric-box">
                    <div className="metric-title">Total Members</div>
                    <div className="metric-value">1,248</div>
                  </div>
                  <div className="metric-box">
                    <div className="metric-title">Active Surveys</div>
                    <div className="metric-value">3</div>
                  </div>
                  <div className="metric-box">
                    <div className="metric-title">Total Responses</div>
                    <div className="metric-value">854</div>
                  </div>
                  <div className="metric-box">
                    <div className="metric-title">Turnout Rate</div>
                    <div className="metric-value">68.4%</div>
                  </div>
                </div>
              )}

              {/* STAFF SURVEY MANAGEMENT */}
              {currentView === 'surveys' && (
                <div className="dashboard-card">
                  <div className="header-actions">
                    <div className="form-title">Survey Management</div>
                    <button className="btn-green-submit" onClick={() => setIsEditSurveyModalOpen(true)}>
                      Edit Active Survey
                    </button>
                  </div>
                  <div className="table-responsive">
                    <table className="data-table">
                      <thead>
                        <tr>
                          <th>Title</th>
                          <th>Opening Date</th>
                          <th>Closing Date</th>
                          <th>Status</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>2026 Cooperative Member Services Survey</td>
                          <td>01/07/2026</td>
                          <td>15/08/2026</td>
                          <td><span style={{ color: '#22c55e', fontWeight: 600 }}>Active</span></td>
                          <td>
                            <button className="btn-sm-action" onClick={() => setIsEditSurveyModalOpen(true)}>Edit</button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              )}

              {/* STAFF RESULTS & ANALYTICS */}
              {currentView === 'analytics' && (
                <div className="dashboard-card">
                  <div className="form-title">Survey Results & Analytics</div>
                  <div className="form-subtitle">2026 Cooperative Member Services Survey (Total Respondents: 589 Members)</div>

                  <div className="chart-container-box">
                    <div style={{ fontWeight: 600, marginBottom: '1.5rem', color: '#444' }}>
                      Most Frequently Used Cooperative Service
                    </div>
                    
                    {/* Donut Chart Visual Representation */}
                    <div className="donut-chart">
                      <div className="donut-hole"></div>
                    </div>

                    <div className="chart-legend">
                      <div className="legend-item">
                        <div className="legend-color" style={{ background: '#137547' }}></div>
                        <span>Savings Deposit (310)</span>
                      </div>
                      <div className="legend-item">
                        <div className="legend-color" style={{ background: '#22c55e' }}></div>
                        <span>Loan Application (185)</span>
                      </div>
                      <div className="legend-item">
                        <div className="legend-color" style={{ background: '#a7f3d0' }}></div>
                        <span>Consumer Store (94)</span>
                      </div>
                    </div>
                  </div>
                </div>
              )}

              {/* STAFF REPORTS */}
              {currentView === 'reports' && (
                <div className="dashboard-card">
                  <div className="form-title">Export Reports</div>
                  <div className="form-subtitle" style={{ marginBottom: '1.5rem' }}>Download official participation & feedback data</div>

                  <div style={{ display: 'flex', gap: '1rem' }}>
                    <button className="btn-green-submit" onClick={() => triggerToast('Export Initiated', 'Downloading CSV Report...')}>
                      Export CSV Summary
                    </button>
                    <button className="btn-green-submit" style={{ background: '#3b82f6' }} onClick={() => triggerToast('Export Initiated', 'Generating PDF Document...')}>
                      Export PDF Executive Summary
                    </button>
                  </div>
                </div>
              )}

              {/* STAFF MEMBER MANAGEMENT */}
              {currentView === 'members' && (
                <div className="dashboard-card">
                  <div className="header-actions">
                    <div className="form-title">Member Accounts Management</div>
                    <button className="btn-green-submit" onClick={() => setIsAddMemberModalOpen(true)}>
                      + Add Member
                    </button>
                  </div>

                  <div className="table-responsive">
                    <table className="data-table">
                      <thead>
                        <tr>
                          <th>Cooperative Account Number</th>
                          <th>Member Name</th>
                          <th>Email</th>
                          <th>Actions</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>Cooperative Account Number</td>
                          <td>Juan Dela Cruz</td>
                          <td>example@gmail.com</td>
                          <td>
                            <button className="btn-sm-action" onClick={() => triggerToast('Password Reset', 'Reset link sent to member email.')}>
                              Reset Password
                            </button>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              )}
            </>
          )}

          {/* ==================== MEMBER VIEWS ==================== */}
          {user.role === 'member' && (
            <>
              {currentView === 'dashboard' && (
                <div>
                  <div className="welcome-banner" style={{ background: 'var(--primary-green)', color: 'white', padding: '2rem', borderRadius: '8px', marginBottom: '1.5rem' }}>
                    <h2>Welcome back, Member!</h2>
                    <p>Participate in cooperative decision-making. Make your voice count!</p>
                  </div>

                  <div className="metrics-row">
                    <div className="metric-box">
                      <div className="metric-title">Available Surveys</div>
                      <div className="metric-value">2</div>
                    </div>
                    <div className="metric-box">
                      <div className="metric-title">Surveys Completed</div>
                      <div className="metric-value">4</div>
                    </div>
                  </div>
                </div>
              )}

              {currentView === 'surveys' && (
                <div className="dashboard-card">
                  <div className="form-title" style={{ marginBottom: '1.5rem' }}>Active Surveys</div>
                  <button className="btn-green-submit" onClick={() => setIsSurveyModalOpen(true)}>
                    Take Cooperative Services Survey
                  </button>
                </div>
              )}

              {currentView === 'profile' && (
                <div className="dashboard-card" style={{ maxWidth: '800px' }}>
                  <div className="form-title">Update Member Profile</div>
                  <form onSubmit={(e) => { e.preventDefault(); triggerToast('Profile Updated', 'Your profile details were updated.'); }}>
                    <div className="form-group" style={{ marginTop: '1rem' }}>
                      <label>Cooperative Account Number</label>
                      <input type="text" className="form-control" value="Cooperative Account Number" disabled readOnly />
                    </div>
                    <div className="form-group">
                      <label>Full Name</label>
                      <input type="text" className="form-control" value={fullName} onChange={(e) => setFullName(e.target.value)} required />
                    </div>
                    <div className="form-group">
                      <label>Email Address</label>
                      <input type="email" className="form-control" value={email} onChange={(e) => setEmail(e.target.value)} required />
                    </div>
                    <button type="submit" className="btn-green-submit">Save Changes</button>
                  </form>
                </div>
              )}

              {currentView === 'password' && (
                <div className="dashboard-card" style={{ maxWidth: '800px' }}>
                  <div className="form-title">Change Password</div>
                  <div className="form-subtitle">Update default password after your first login.</div>
                  <form onSubmit={(e) => { e.preventDefault(); triggerToast('Password Changed', 'Your password was successfully updated.'); }}>
                    <div className="form-group">
                      <label>Current Password</label>
                      <input type="password" className="form-control" required />
                    </div>
                    <div className="form-group">
                      <label>New Password</label>
                      <input type="password" className="form-control" required />
                    </div>
                    <div className="form-group">
                      <label>Confirm New Password</label>
                      <input type="password" className="form-control" required />
                    </div>
                    <button type="submit" className="btn-green-submit">Update Password</button>
                  </form>
                </div>
              )}
            </>
          )}
        </div>
      </div>

      {/* ---------------- EDIT SURVEY MODAL (STAFF) ---------------- */}
      {isEditSurveyModalOpen && (
        <div className="modal-backdrop">
          <div className="modal-card">
            <div className="modal-title">Edit Survey</div>
            <form onSubmit={(e) => { e.preventDefault(); setIsEditSurveyModalOpen(false); triggerToast('Survey Saved', 'Survey dates updated successfully.'); }}>
              <div className="form-group">
                <label>Survey Title</label>
                <input type="text" className="form-control" defaultValue="2026 Cooperative Member Services Survey" required />
              </div>
              <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '1rem' }}>
                <div className="form-group">
                  <label>Opening Date</label>
                  <input type="text" className="form-control" defaultValue="01/07/2026" required />
                </div>
                <div className="form-group">
                  <label>Closing Date</label>
                  <input type="text" className="form-control" defaultValue="15/08/2026" required />
                </div>
              </div>
              <div className="modal-actions">
                <button type="button" className="btn-cancel" onClick={() => setIsEditSurveyModalOpen(false)}>Cancel</button>
                <button type="submit" className="btn-green-submit">Save Changes</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* ---------------- TAKE SURVEY MODAL (MEMBER) ---------------- */}
      {isSurveyModalOpen && (
        <div className="modal-backdrop">
          <div className="modal-card">
            <div className="modal-title">Cooperative Services Survey</div>
            <form onSubmit={(e) => { e.preventDefault(); setIsSurveyModalOpen(false); triggerToast('Response Saved', 'Thank you for completing the survey!'); }}>
              <div className="survey-question-block">
                <div className="question-text">1. Which cooperative service do you use most frequently?</div>
                <label className="radio-option"><input type="radio" name="q1" required /> Savings Deposit</label>
                <label className="radio-option"><input type="radio" name="q1" /> Loan Application</label>
                <label className="radio-option"><input type="radio" name="q1" /> Consumer Store</label>
              </div>
              <div className="modal-actions">
                <button type="button" className="btn-cancel" onClick={() => setIsSurveyModalOpen(false)}>Cancel</button>
                <button type="submit" className="btn-submit-response">Submit Responses</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* ---------------- ADD MEMBER MODAL (STAFF) ---------------- */}
      {isAddMemberModalOpen && (
        <div className="modal-backdrop">
          <div className="modal-card">
            <div className="modal-title">Add New Member</div>
            <form onSubmit={(e) => { e.preventDefault(); setIsAddMemberModalOpen(false); triggerToast('Member Added', 'New member account created successfully.'); }}>
              <div className="form-group">
                <label>Account Number</label>
                <input type="text" className="form-control" placeholder="e.g. ACC-2026" required />
              </div>
              <div className="form-group">
                <label>Full Name</label>
                <input type="text" className="form-control" placeholder="e.g. Maria Santos" required />
              </div>
              <div className="form-group">
                <label>Email Address</label>
                <input type="email" className="form-control" placeholder="e.g. maria@gmail.com" required />
              </div>
              <div className="modal-actions">
                <button type="button" className="btn-cancel" onClick={() => setIsAddMemberModalOpen(false)}>Cancel</button>
                <button type="submit" className="btn-green-submit">Create Account</button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* ---------------- TOAST ALERTS ---------------- */}
      {toastMessage && (
        <div className="toast-box">
          <div>
            <div className="toast-header-text">{toastMessage.title}</div>
            <div className="toast-body-text">{toastMessage.message}</div>
          </div>
          <button className="toast-close-btn" onClick={() => setToastMessage(null)}>&times;</button>
        </div>
      )}
    </div>
  );
}