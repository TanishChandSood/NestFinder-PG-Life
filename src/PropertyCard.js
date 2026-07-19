import React, { useState } from 'react';

function PropertyCard({ property }) {
    // 1. Amenities toggle karne ka state
    const [showDetails, setShowDetails] = useState(false);
    
    // 2. Heart icon toggle karne ka state (default true kyunki ye shortlist page hai)
    const [isFavorite, setIsFavorite] = useState(true);

    return (
        <div className="card shadow-sm mb-4" style={{ borderRadius: '12px', overflow: 'hidden', border: '1px solid #e0e0e0', maxWidth: '800px', margin: '0 auto' }}>
            <div className="row no-gutters align-items-center">
                <div className="col-md-5">
                    <img 
                        src={property.image} 
                        alt={property.name} 
                        className="img-fluid w-100" 
                        style={{ objectFit: 'cover', height: '240px' }} 
                    />
                </div>
                <div className="col-md-7 p-4">
                    <div className="d-flex justify-content-between align-items-start">
                        <div>
                            <div className="text-warning mb-1">
                                {"⭐".repeat(Math.floor(property.rating))}
                                {property.rating % 1 !== 0 ? "⭐" : ""}
                            </div>
                            <h4 className="font-weight-bold text-dark mb-1">{property.name}</h4>
                            <p className="text-muted small mb-2">{property.address}</p>
                        </div>
                        
                        {/* ❤️ Heart Toggle Button */}
                        <span 
                            style={{ 
                                fontSize: '24px', 
                                cursor: 'pointer', 
                                userSelect: 'none',
                                transition: 'transform 0.1s ease-in-out'
                            }} 
                            onClick={() => setIsFavorite(!isFavorite)}
                            onMouseDown={(e) => e.target.style.transform = 'scale(0.8)'}
                            onMouseUp={(e) => e.target.style.transform = 'scale(1)'}
                            title={isFavorite ? "Remove from Shortlist" : "Add to Shortlist"}
                        >
                            {isFavorite ? '❤️' : '🤍'}
                        </span>
                    </div>
                    
                    <div className="my-2">
                        <span className="badge badge-light p-2" style={{ border: '1px solid #ddd', borderRadius: '20px' }}>
                            {property.gender === 'Female' ? '👩 Female Only' : '👨 Male Only'}
                        </span>
                    </div>

                    {/* Amenities Section */}
                    {showDetails && (
                        <div className="mt-3 p-2 bg-light rounded" style={{ fontSize: '13px', borderLeft: '4px solid #4dbda5' }}>
                            <strong>Included Amenities:</strong>
                            <div className="d-flex gap-3 mt-1 flex-wrap">
                                <span className="mr-3">📶 Free Wi-Fi</span>
                                <span className="mr-3">❄️ AC Available</span>
                                <span className="mr-3">🍱 3-Time Meals</span>
                                <span>🧹 Daily Cleaning</span>
                            </div>
                        </div>
                    )}

                    <div className="d-flex justify-content-between align-items-center mt-3">
                        <span className="font-weight-bold h5 mb-0">
                            ₹ {property.price}/- <span style={{ fontSize: '12px', fontWeight: 'normal', color: '#666' }}>per month</span>
                        </span>
                        <button 
                            className="btn px-4 text-white" 
                            style={{ backgroundColor: '#4dbda5', borderRadius: '4px', border: 'none', fontWeight: '500' }}
                            onClick={() => setShowDetails(!showDetails)}
                        >
                            {showDetails ? "Hide Details" : "View Details"}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default PropertyCard;