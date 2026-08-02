from fastapi import FastAPI, HTTPException, Security
from fastapi.security import HTTPBearer, HTTPAuthorizationCredentials
from fastapi.middleware.cors import CORSMiddleware
import subprocess
import json
import os

# ============ CONFIGURATION ============
API_KEY = os.environ.get("ODNELAZM_API_KEY")

# ============ APP SETUP ============
app = FastAPI(title="Odnelazm API", description="API for Kenyan Parliamentary Data")

# Security
security = HTTPBearer()

# Enable CORS for your cPanel domain
app.add_middleware(
    CORSMiddleware,
    allow_origins=["https://nikokadi.digitallyfit.top"],
    allow_methods=["*"],
    allow_headers=["*"],
    allow_credentials=True,
)

ODNELAZM_PATH = "/root/odnelazm/target/release/odnelazm"

# ============ HELPER FUNCTIONS ============
def run_odnelazm(command: list[str]) -> dict:
    """Run odnelazm command and return JSON result"""
    try:
        result = subprocess.run(
            command,
            shell=False,
            capture_output=True,
            text=True,
            timeout=120
        )
        
        if result.returncode != 0:
            raise Exception("Command failed")
        
        # Try to parse JSON
        try:
            return json.loads(result.stdout)
        except json.JSONDecodeError:
            # If not JSON, return as raw text
            return {"raw_output": result.stdout}
            
    except subprocess.TimeoutExpired:
        raise Exception("Command timed out after 120 seconds")
    except Exception as e:
        raise Exception("Odnelazm execution failed")

def verify_api_key(credentials: HTTPAuthorizationCredentials = Security(security)):
    """Verify the API key"""
    if not API_KEY or credentials.credentials != API_KEY:
        raise HTTPException(
            status_code=403, 
            detail="Invalid or missing API Key. Please provide a valid Bearer token."
        )
    return credentials.credentials

# ============ PUBLIC ENDPOINTS ============
@app.get("/")
def root():
    return {
        "status": "ok", 
        "service": "Odnelazm API",
        "version": "1.0",
        "endpoints": {
            "GET /": "This help message",
            "GET /members": "Get all members (requires API key)",
            "GET /members/{slug}": "Get specific member profile (requires API key)",
            "GET /members/house/{house}": "Get members by house - national-assembly or senate (requires API key)"
        }
    }

@app.get("/health")
def health_check():
    """Simple health check endpoint"""
    return {"status": "healthy", "odnelazm_path": ODNELAZM_PATH}

# ============ PROTECTED ENDPOINTS ============
@app.get("/members")
def get_all_members(api_key: str = Security(verify_api_key)):
    """Get all members of the current parliament"""
    try:
        data = run_odnelazm([ODNELAZM_PATH, "all-members", "--output", "json"])
        return {"success": True, "data": data}
    except Exception:
        raise HTTPException(status_code=502, detail="Parliament data request failed")

@app.get("/members/{slug}")
def get_member_profile(
    slug: str,
    house: str | None = None,
    api_key: str = Security(verify_api_key),
):
    """Get a member profile, preferring the caller's validated house."""
    valid_houses = ["national-assembly", "senate"]
    if house is not None and house not in valid_houses:
        raise HTTPException(status_code=422, detail="Invalid parliamentary house")

    houses = valid_houses
    if house:
        houses = [house, *[candidate for candidate in valid_houses if candidate != house]]

    for candidate_house in houses:
        source_url = (
            "https://mzalendo.com/mps-performance/"
            f"{candidate_house}/13th-parliament/{slug}/"
        )
        try:
            data = run_odnelazm(
                [ODNELAZM_PATH, "profile", source_url, "--output", "json"]
            )
            return {
                "success": True,
                "data": data,
                "source_url": source_url,
                "source_house": candidate_house,
            }
        except Exception:
            continue

    raise HTTPException(status_code=502, detail="Member profile lookup failed")

@app.get("/members/house/{house}")
def get_members_by_house(house: str, api_key: str = Security(verify_api_key)):
    """Get members by house (national-assembly or senate)"""
    # Validate house parameter
    if house not in ["national-assembly", "senate"]:
        raise HTTPException(
            status_code=400, 
            detail="Invalid house. Use 'national-assembly' or 'senate'"
        )
    
    try:
        data = run_odnelazm([ODNELAZM_PATH, "members", "--house", house, "--output", "json"])
        return {"success": True, "data": data}
    except Exception:
        raise HTTPException(status_code=502, detail="Parliament data request failed")

@app.get("/profile/{slug}")
def get_profile_alternative(slug: str, api_key: str = Security(verify_api_key)):
    """Alternative endpoint for getting member profile (alias for /members/{slug})"""
    return get_member_profile(slug, api_key=api_key)

# ============ RUN SERVER ============
if __name__ == "__main__":
    import uvicorn
    print(f"🚀 Starting Odnelazm API Server...")
    print("Authentication: bearer token loaded from environment")
    print(f"🔗 Endpoint: http://0.0.0.0:8000")
    uvicorn.run(app, host="0.0.0.0", port=8000)