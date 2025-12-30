import React from "react";
import ReactDOM from "react-dom/client";
import App from "./App.jsx";
import { BrowserRouter } from "react-router-dom"; 
import { authProvider } from "./context/authContext.jsx"; 

import "./index.css";

ReactDOM.createRoot(document.getElementById("root")).render(
  <React.StrictMode>
    <authProvider> {/* bọc AuthProvider trước BrowserRouter */}
      <BrowserRouter>
        <App />
      </BrowserRouter>
    </authProvider>
  </React.StrictMode>
);
