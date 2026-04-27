import { createRoot } from 'react-dom/client';
import { Component } from 'react';
import './index.css';
import App from './App.jsx';

class ErrorBoundary extends Component {
  state = { erreur: null };

  static getDerivedStateFromError(e) {
    return { erreur: e };
  }

  render() {
    if (this.state.erreur) {
      return (
        <div style={{ padding: '20px', fontFamily: 'monospace', color: 'red' }}>
          <h2>Erreur React</h2>
          <pre style={{ whiteSpace: 'pre-wrap' }}>{this.state.erreur.message}</pre>
          <pre style={{ whiteSpace: 'pre-wrap', fontSize: '12px', color: '#666' }}>
            {this.state.erreur.stack}
          </pre>
        </div>
      );
    }
    return this.props.children;
  }
}

try {
  createRoot(document.getElementById('root')).render(
    <ErrorBoundary>
      <App />
    </ErrorBoundary>
  );
} catch (e) {
  document.getElementById('root').innerHTML =
    `<div style="padding:20px;color:red;font-family:monospace">
      <h2>Erreur d'initialisation</h2>
      <pre>${e.message}</pre>
      <pre style="font-size:12px;color:#666">${e.stack}</pre>
    </div>`;
}
