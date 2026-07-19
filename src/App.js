import React, { useState } from 'react';
import PropertyCard from './PropertyCard';

function App() {
  // Aapke public/img folder ki real photos ka naam yahan set kar diya hai
  const [interestedProperties] = useState([
    {
      id: 1,
      name: "Stanza Living Shanti Niketan",
      address: "Plot 42, Gorai Road, Borivali West, Mumbai 400092",
      price: "9,000",
      rating: 4.5,
      gender: "Female",
      // Pehli photo ka naam (extension .jpg ya .png check kar lena)
      image: "/img/1782856081_images.jpg" 
    },
    {
      id: 2,
      name: "Skyline Luxury Living",
      address: "Sector 2, Near Link Road, Andheri West, Mumbai 400053",
      price: "12,000",
      rating: 4.8,
      gender: "Female",
      // Dusri photo ka naam
      image: "/img/1782857938_images (1).jpg" 
    }
  ]);

  return (
    <div className="container my-5" style={{ fontFamily: 'sans-serif' }}>
      <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
      
      <div className="text-center mb-5">
        <h2 className="font-weight-bold" style={{ fontSize: '32px', color: '#333' }}>
          My Shortlisted Accommodations
        </h2>
        <span className="badge p-2 mt-2 text-white" style={{ backgroundColor: '#4dbda5', fontSize: '14px' }}>
          Interactive Component Architecture ⚡
        </span>
      </div>

      <div className="row justify-content-center">
        <div className="col-12 col-lg-10">
          {interestedProperties.map(property => (
            <PropertyCard key={property.id} property={property} />
          ))}
        </div>
      </div>
    </div>
  );
}

export default App;