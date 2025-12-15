import React, { useEffect, useState } from "react";
import { getAllSans } from "../../services/sanService";
import SanCard from "../../components/SanCard";

export default function SanList() {
  const [sans, setSans] = useState([]);

  useEffect(() => {
    getAllSans()
      .then(res => setSans(res.data))
      .catch(err => console.log(err));
  }, []);

  return (
    <div>
      <h1>Danh sách sân</h1>
      {sans.length === 0 && <p>Chưa có sân nào.</p>}
      {sans.map(san => <SanCard key={san.id} san={san} />)}
    </div>
  );
}
