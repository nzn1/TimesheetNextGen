<?php
if(!class_exists('Site'))die('Restricted Access');
if(Auth::ACCESS_GRANTED != $this->requestPageAuth('Open'))return;
PageElements::setHead("<title>".Config::getMainTitle()." | Privacy Policy</title>");
?>

<div class = "col50"><!-- Place content in a div tag to allow complete padding etc --> 
<div class = "pad5">   			

<h1>Privacy Policy</h1>

<p>We take great care to preserve and protect the privacy of all visitors to this website. This privacy policy should be read in conjunction with our terms and conditions and details our policy towards the collection, use and storage of visitor information.</p>

<p>We do not collect personal information from any visitor unless it is expressly provided to us. We do not share, sell or otherwise provide personal data to third parties in any way other than as detailed below.</p>

<h3>When Is Information Collected?</h3>

<p>When you visit and navigate around our website we collect statistical information relating to site usage. This information does not include any personally identifiable information.</p>


<h3>Use of Your Information</h3>

<p>As explained above we use your information to provide the service that you have requested to you. We do not pass your personal information to any other third party unless we are required by law to do so. We do not under any circumstances rent, sell or otherwise provide your data to third parties for their marketing purposes.</p>

                  
<hr />    	
</div>
<!-- Close padding div -->
</div>
<!-- Close col50 div -->
<div class="col50 right">
  <div class="pad5">
  <h3>Access to Information</h3>

<p>You can request a copy of any information that we hold about you. We reserve the right to charge an administrative charge of up to £10 in the event of such a request.</p>

<h3>Cookies</h3>

<p>A cookie is a piece of data sent to a web site user's browser and stored on a user's hard drive tied to session information about the user. On our web site, cookies are used only as an aid to the user experience to enable a user's identity to be remembered should they be logged in.  No personally identifiable information is stored in the cookie.</p>

<h3>Other web sites</h3>

<p>We cannot be responsible for the privacy practices of third parties whose web sites may be referenced within our site.</p>

<h3>Contacting Us</h3>

<p>You can contact us by email, telephone or by post. Our contact details are available on the <a href="<?php echo Config::getRelativeRoot(); ?>/contact/">Contact</a> page</p>
  </div><!--end pad5-->
</div><!--end col50 right-->
